<?php

namespace App\Modules\Access\Models;

use App\Models\User;
use App\Modules\Access\Support\ScopeType;
use App\Modules\Platform\Support\HasLockVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One profile or one capability given to one user in one scope (DATA_MODEL section 9). A privileged grant waits in
 * PENDING_APPROVAL until a second person approves it; a revoked or expired grant stays as history.
 *
 * @property int $id
 * @property string $public_id
 * @property int $user_id
 * @property int|null $profile_id
 * @property string|null $capability
 * @property ScopeType $scope_type
 * @property int|null $scope_id
 * @property string|null $scope_key
 * @property bool $requires_approval
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 * @property string $reason
 * @property int $granted_by
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property string $status
 * @property int|null $revoked_by
 * @property Carbon|null $revoked_at
 * @property string|null $revoke_reason
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AccessGrant extends Model
{
    use HasLockVersion, HasUlids;

    public const PENDING_APPROVAL = 'PENDING_APPROVAL';

    public const ACTIVE = 'ACTIVE';

    public const REVOKED = 'REVOKED';

    public const EXPIRED = 'EXPIRED';

    protected $table = 'access_grants';

    protected $guarded = ['id'];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'lock_version' => 0,
    ];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /**
     * The status as of a day: a pending or active grant whose end has come is EXPIRED, as the evaluator already treats it.
     */
    public function statusOn(string $day): string
    {
        if (in_array($this->status, [self::PENDING_APPROVAL, self::ACTIVE], true)
            && $this->effective_to !== null
            && $this->effective_to->toDateString() <= $day) {
            return self::EXPIRED;
        }

        return $this->status;
    }

    /**
     * The grants whose status as of a day is the given one ({@see statusOn()}).
     *
     * @param  Builder<AccessGrant>  $query
     * @return Builder<AccessGrant>
     */
    public function scopeWithStatusOn(Builder $query, string $status, string $day): Builder
    {
        if ($status === self::EXPIRED) {
            return $query->where(fn ($query) => $query
                ->where('status', self::EXPIRED)
                ->orWhere(fn ($query) => $query
                    ->whereIn('status', [self::PENDING_APPROVAL, self::ACTIVE])
                    ->where('effective_to', '<=', $day)));
        }

        $query->where('status', $status);

        if (in_array($status, [self::PENDING_APPROVAL, self::ACTIVE], true)) {
            $query->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>', $day));
        }

        return $query;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<AccessProfile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(AccessProfile::class, 'profile_id');
    }

    protected function casts(): array
    {
        return [
            'scope_type' => ScopeType::class,
            'requires_approval' => 'boolean',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'approved_at' => 'datetime',
            'revoked_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }
}
