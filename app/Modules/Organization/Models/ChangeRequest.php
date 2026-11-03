<?php

namespace App\Modules\Organization\Models;

use App\Models\User;
use App\Modules\Platform\Support\HasLockVersion;
use App\Modules\Platform\Support\RequestId;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A change request of WF-029 families F1 (unit structure and history) and F2 (positions and appointments). Nothing changes
 * the organization before the request is EFFECTIVE (WF029-S5).
 *
 * @property int $id
 * @property string $public_id
 * @property string $family
 * @property string $kind
 * @property int|null $org_unit_id
 * @property string $status
 * @property array<string, mixed> $payload
 * @property string $reason
 * @property string|null $source_ref
 * @property Carbon $proposed_effective_from
 * @property array{checked_at: string, issues: list<array{code: string, blocking: bool}>}|null $validation
 * @property Carbon|null $effective_from
 * @property string|null $decision_ref
 * @property bool $is_simulation
 * @property int $created_by
 * @property int|null $submitted_by
 * @property Carbon|null $submitted_at
 * @property int|null $decided_by
 * @property Carbon|null $decided_at
 * @property int|null $applied_by
 * @property Carbon|null $applied_at
 * @property string|null $closed_reason
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ChangeRequest extends Model
{
    use HasLockVersion, HasUlids;

    public const F1 = 'F1';

    public const F2 = 'F2';

    // The states of WF-029 (STATE_TRANSITIONS): S1 to S6, and S7 as WITHDRAWN or REJECTED.
    public const DRAFT = 'DRAFT';

    public const VALIDATED = 'VALIDATED';

    public const AWAITING_DECISION = 'AWAITING_DECISION';

    public const APPROVED = 'APPROVED';

    public const EFFECTIVE = 'EFFECTIVE';

    public const SUPERSEDED = 'SUPERSEDED';

    public const WITHDRAWN = 'WITHDRAWN';

    public const REJECTED = 'REJECTED';

    public const CREATE_UNIT = 'CREATE_UNIT';

    public const UPDATE_UNIT = 'UPDATE_UNIT';

    public const MOVE_UNIT = 'MOVE_UNIT';

    public const DEACTIVATE_UNIT = 'DEACTIVATE_UNIT';

    public const ACTIVATE_UNIT = 'ACTIVATE_UNIT';

    public const CREATE_POSITION = 'CREATE_POSITION';

    public const APPOINT = 'APPOINT';

    public const END_APPOINTMENT = 'END_APPOINTMENT';

    /**
     * The kinds of each family.
     *
     * @var array<string, list<string>>
     */
    public const KINDS = [
        self::F1 => [self::CREATE_UNIT, self::UPDATE_UNIT, self::MOVE_UNIT, self::DEACTIVATE_UNIT, self::ACTIVATE_UNIT],
        self::F2 => [self::CREATE_POSITION, self::APPOINT, self::END_APPOINTMENT],
    ];

    /**
     * The states from which TR-288 withdraws or supersedes the request (S1 to S4).
     *
     * @var list<string>
     */
    public const OPEN = [self::DRAFT, self::VALIDATED, self::AWAITING_DECISION, self::APPROVED];

    protected $table = 'org_change_requests';

    protected $guarded = ['id'];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'lock_version' => 0,
        'is_simulation' => false,
    ];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public static function familyOf(string $kind): string
    {
        return in_array($kind, self::KINDS[self::F1], true) ? self::F1 : self::F2;
    }

    /**
     * @return BelongsTo<OrgUnit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'org_unit_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * @return HasMany<ChangeRequestTransition, $this>
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(ChangeRequestTransition::class);
    }

    /**
     * Whether validation found an issue that stops the request from going on.
     */
    public function hasBlockingIssues(): bool
    {
        foreach ($this->validation['issues'] ?? [] as $issue) {
            if ($issue['blocking']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Write one transition of WF-029 with the state change, in the transaction of the Action that changes the state.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function recordTransition(string $code, ?string $from, User $actor, ?string $reason = null, ?array $metadata = null): void
    {
        $this->transitions()->create([
            'transition_code' => $code,
            'from_state' => $from,
            'to_state' => $this->status,
            'actor_user_id' => $actor->getKey(),
            'acted_at' => now(),
            'reason' => $reason,
            'is_simulation' => $this->is_simulation,
            'metadata' => $metadata,
            'request_id' => app()->bound('request') ? RequestId::of(request()) : null,
        ]);
    }

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'validation' => 'array',
            'proposed_effective_from' => 'date',
            'effective_from' => 'date',
            'is_simulation' => 'boolean',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
            'applied_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }
}
