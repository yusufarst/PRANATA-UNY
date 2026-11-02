<?php

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Exceptions\CommandBusyException;
use App\Modules\Platform\Support\Action;
use App\Modules\Platform\Support\CommandMessage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Sleep;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * X-Idempotency-Key for material commands (COMMAND_AND_API_CONTRACTS section 4), as the route middleware "idempotent".
 *
 * The key is bound to the signed-in user. The claim commits before the command runs, so a second request with the same key
 * and the same input waits briefly for the first and then gets its redirect again; the command itself runs once. The same
 * key with different input is refused with 422. A command that failed before its Action committed changed nothing, so its key
 * may be sent again.
 */
class EnsureIdempotentCommand
{
    public const HEADER = 'X-Idempotency-Key';

    public const TABLE = 'platform_idempotency_keys';

    public const TTL_HOURS = 24;

    /**
     * How long a duplicate waits for the first request to finish before it gets 409: 20 checks, 250 ms apart.
     */
    private const WAIT_CHECKS = 20;

    private const WAIT_MILLISECONDS = 250;

    private const UUID_V4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Reading commands repeat safely, and a guest command has no user to bind the key to.
        if ($user === null || $request->isMethodSafe()) {
            return $next($request);
        }

        $key = strtolower((string) $request->headers->get(self::HEADER));

        if (preg_match(self::UUID_V4, $key) !== 1) {
            throw ValidationException::withMessages(['_idempotency' => [CommandMessage::of('idempotency_missing')]]);
        }

        $userId = $user->getAuthIdentifier();
        $route = mb_substr($request->route()?->getName() ?? $request->method().' /'.ltrim($request->path(), '/'), 0, 150);
        $hash = self::fingerprint($request);

        for ($check = 0; ; $check++) {
            $row = $this->claim($userId, $key, $route, $hash);

            if ($row === null) {
                break;
            }

            if ($row->route_name !== $route || $row->request_sha256 !== $hash) {
                throw ValidationException::withMessages(['_idempotency' => [CommandMessage::of('idempotency_reused')]]);
            }

            if ($row->status === 'COMPLETED') {
                return self::replay($request, $row->result);
            }

            if ($check >= self::WAIT_CHECKS) {
                throw CommandBusyException::inProgress();
            }

            Sleep::for(self::WAIT_MILLISECONDS)->milliseconds();
        }

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->finishFailed($request, $userId, $key, null);

            throw $exception;
        }

        if (self::succeeded($request, $response)) {
            $this->finish($userId, $key, 'COMPLETED', [
                'status' => $response->getStatusCode(),
                'location' => $response instanceof RedirectResponse ? $response->getTargetUrl() : null,
            ]);
        } else {
            $this->finishFailed($request, $userId, $key, $response->getStatusCode());
        }

        return $response;
    }

    /**
     * A failure before any commit left no effect, so the key may be sent again. A failure after a command committed, such as
     * an after-commit callback that threw, keeps the key completed: a resend goes back instead of running the writes twice.
     */
    private function finishFailed(Request $request, mixed $userId, string $key, ?int $status): void
    {
        if ($request->attributes->get(Action::COMMITTED) === true) {
            $this->finish($userId, $key, 'COMPLETED', ['status' => $status, 'location' => null]);

            return;
        }

        $this->finish($userId, $key, 'FAILED', null);
    }

    /**
     * Claim the key for this request, or give the row of the request that holds it.
     */
    private function claim(mixed $userId, string $key, string $route, string $hash): ?object
    {
        $now = now();
        $claim = [
            'route_name' => $route,
            'request_sha256' => $hash,
            'status' => 'IN_PROGRESS',
            'result' => null,
            'created_at' => $now,
            'expires_at' => $now->copy()->addHours(self::TTL_HOURS),
        ];

        $inserted = DB::table(self::TABLE)->insertOrIgnore(['user_id' => $userId, 'idempotency_key' => $key, ...$claim]);

        if ($inserted === 1) {
            return null;
        }

        $row = DB::table(self::TABLE)->where('user_id', $userId)->where('idempotency_key', $key)->first();

        if ($row === null) {
            return $this->claim($userId, $key, $route, $hash);
        }

        // A failed or expired claim left no effect, so this request takes the key over; the condition stops two from doing so.
        $taken = DB::table(self::TABLE)
            ->where('id', $row->id)
            ->where(fn ($query) => $query->where('status', 'FAILED')->orWhere('expires_at', '<=', $now))
            ->update($claim);

        return $taken === 1 ? null : $row;
    }

    /**
     * @param  array<string, mixed>|null  $result
     */
    private function finish(mixed $userId, string $key, string $status, ?array $result): void
    {
        DB::table(self::TABLE)
            ->where('user_id', $userId)
            ->where('idempotency_key', $key)
            ->where('status', 'IN_PROGRESS')
            ->update(['status' => $status, 'result' => $result === null ? null : json_encode($result, JSON_THROW_ON_ERROR)]);
    }

    /**
     * A command succeeded when it answered 2xx, or redirected without flashing validation errors in this request.
     */
    private static function succeeded(Request $request, Response $response): bool
    {
        if ($response->isSuccessful()) {
            return true;
        }

        if (! $response->isRedirection()) {
            return false;
        }

        if (! $request->hasSession()) {
            return true;
        }

        // Errors flashed by the request before are still readable now, so only a flash of this request counts.
        $session = $request->session();
        $flashed = $session->get('_flash.new', []);
        $errors = $session->get('errors');

        return ! (is_array($flashed) && in_array('errors', $flashed, true) && $errors instanceof ViewErrorBag && $errors->any());
    }

    /**
     * Send the client where the first request sent it.
     */
    private static function replay(Request $request, mixed $result): Response
    {
        $stored = is_string($result) ? json_decode($result, true) : null;
        $location = is_array($stored) && is_string($stored['location'] ?? null) ? $stored['location'] : null;

        $response = $location === null ? back(303) : redirect()->to($location, 303);
        $response->headers->set('X-Idempotent-Replay', 'true');

        return $response;
    }

    /**
     * The SHA-256 of what the command was asked to do: method, address and input, with keys sorted and files by content.
     */
    public static function fingerprint(Request $request): string
    {
        return hash('sha256', json_encode([
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'input' => self::canonical($request->except('_token')),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    private static function canonical(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            $path = $value->getRealPath();

            return [
                'name' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
                'sha256' => $path === false ? null : hash_file('sha256', $path),
            ];
        }

        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value, SORT_STRING);
        }

        return array_map(self::canonical(...), $value);
    }
}
