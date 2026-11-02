<?php

namespace App\Modules\Audit\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Writes one row of audit_events (DATA_MODEL section 12, SECURITY_MODEL section 9).
 *
 * The row is written on the default connection, so it joins the transaction of the calling Action and is rolled back with
 * it. Nothing is caught here: a failed write fails the Action (fail closed). Changes and metadata never carry a password,
 * token or other secret; a key that looks like one is refused, at any depth.
 */
final class AuditLogger
{
    /**
     * The request attribute that carries the correlation id of the current request.
     */
    public const REQUEST_ID = 'request_id';

    /**
     * A key part that marks a secret, matched on the snake_case key split at underscores.
     */
    private const SECRET_KEY = '/(^|_)(password|passwd|secret|token|otp|totp|recovery_codes?|api_key|private_key|cookie|authorization|credentials?)(_|$)/';

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuthFactory $auth,
        private readonly Container $container,
    ) {}

    /**
     * Record an audit event.
     *
     * @param  string  $action  dotted code, for example identity.login_failed
     * @param  string  $module  the module that performs the action, for example identity
     * @param  array<string, mixed>  $changes  changed fields, as ['field' => ['before' => …, 'after' => …]]
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        string $module,
        ?Model $subject = null,
        array $changes = [],
        ?string $reason = null,
        array $metadata = [],
        ?string $workspace = null,
        ?Authenticatable $actor = null,
    ): void {
        if (preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+\z/', $action) !== 1 || strlen($action) > 100) {
            throw new InvalidArgumentException("[{$action}] is not an audit action code.");
        }

        if (preg_match('/^[a-z][a-z0-9_]{0,29}\z/', $module) !== 1) {
            throw new InvalidArgumentException("[{$module}] is not a module code.");
        }

        // Checked as stored: json_encode() also writes the public properties and jsonSerialize() of an object.
        $changes = self::plain($changes);
        $metadata = self::plain($metadata);
        $secrets = [...self::secretKeys($changes), ...self::secretKeys($metadata)];

        if ($secrets !== []) {
            throw new InvalidArgumentException('Audit events never carry secrets; refused: '.implode(', ', $secrets).'.');
        }

        $actor ??= $this->auth->guard()->user();
        $request = $this->request();
        $userAgent = $request?->userAgent();

        $this->database->connection()->table('audit_events')->insert([
            'actor_user_id' => $actor?->getAuthIdentifier(),
            'actor_role_group' => self::attribute($actor, 'role_group'),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject?->getTable(),
            'subject_id' => $subject?->getKey(),
            'subject_public_id' => self::attribute($subject, 'public_id'),
            'workspace' => $workspace,
            'reason' => $reason,
            'changes' => self::json($changes),
            'metadata' => self::json($metadata),
            'request_id' => $request === null ? null : self::requestId($request),
            'ip' => $request?->ip(),
            'user_agent' => $userAgent === null ? null : mb_substr($userAgent, 0, 255),
        ]);
    }

    /**
     * The paths of the keys that look like secrets, at any depth.
     *
     * @param  array<array-key, mixed>  $values
     * @return list<string>
     */
    public static function secretKeys(array $values, string $prefix = ''): array
    {
        $found = [];

        foreach ($values as $key => $value) {
            $path = $prefix.$key;

            if (is_string($key) && self::namesSecret($key)) {
                $found[] = $path;
            }

            if (is_array($value)) {
                array_push($found, ...self::secretKeys($value, $path.'.'));
            }
        }

        return $found;
    }

    /**
     * Whether a key names a secret, read as camelCase words and as written (API_KEY, X-Api-Key).
     */
    private static function namesSecret(string $key): bool
    {
        foreach ([Str::snake($key), strtolower($key)] as $form) {
            if (preg_match(self::SECRET_KEY, (string) preg_replace('/[^a-z0-9]+/', '_', $form)) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * The correlation id of the request: one per request, shared by every event the request records.
     */
    public static function requestId(Request $request): string
    {
        $id = $request->attributes->get(self::REQUEST_ID);

        if (! is_string($id) || ! Str::isUuid($id)) {
            $id = (string) Str::uuid();
            $request->attributes->set(self::REQUEST_ID, $id);
        }

        return $id;
    }

    private function request(): ?Request
    {
        if (! $this->container->bound('request')) {
            return null;
        }

        return $this->container->make('request');
    }

    /**
     * Read a stored attribute without touching accessors, relations or the missing attribute check.
     */
    private static function attribute(mixed $model, string $key): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        $value = $model->getAttributes()[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * The payload as json_encode() stores it, with every object turned into an array.
     *
     * @param  array<array-key, mixed>  $values
     * @return array<array-key, mixed>
     */
    private static function plain(array $values): array
    {
        $plain = json_decode(json_encode($values, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        return is_array($plain) ? $plain : [];
    }

    /**
     * @param  array<array-key, mixed>  $values
     */
    private static function json(array $values): ?string
    {
        return $values === [] ? null : json_encode($values, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
