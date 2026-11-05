<?php

namespace App\Modules\Access\Models;

use App\Models\User;
use App\Modules\Platform\Support\HasLockVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One user in one assignment role on one object of a module (DATA_MODEL section 9, ACCESS_CONTROL section 5). An ACTIVE
 * assignment is in force from effective_from until effective_to, and lapses after it; an ENDED or REVOKED one stays as history.
 *
 * @property int $id
 * @property string $public_id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $assignment_role
 * @property int $user_id
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 * @property string|null $decree_ref
 * @property array<string, mixed> $sod_check
 * @property int $assigned_by
 * @property string $status
 * @property int|null $closed_by
 * @property Carbon|null $closed_at
 * @property string|null $close_reason
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProcessAssignment extends Model
{
    use HasLockVersion, HasUlids;

    public const ACTIVE = 'ACTIVE';

    public const ENDED = 'ENDED';

    public const REVOKED = 'REVOKED';

    protected $table = 'process_assignments';

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
     * The assignments in force at this moment: ACTIVE, begun and not yet past their end.
     *
     * @param  Builder<ProcessAssignment>  $query
     * @return Builder<ProcessAssignment>
     */
    public function scopeInForce(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('status', self::ACTIVE)
            ->where('effective_from', '<=', $now)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>', $now));
    }

    /**
     * The assignments not over yet: ACTIVE and not past their end, in force now or waiting for their first day. An ACTIVE
     * row past its end has lapsed and counts as history.
     *
     * @param  Builder<ProcessAssignment>  $query
     * @return Builder<ProcessAssignment>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('status', self::ACTIVE)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>', $now));
    }

    /**
     * The ACTIVE assignments past their end.
     *
     * @param  Builder<ProcessAssignment>  $query
     * @return Builder<ProcessAssignment>
     */
    public function scopeLapsed(Builder $query): Builder
    {
        return $query->where('status', self::ACTIVE)->where('effective_to', '<=', now());
    }

    /**
     * @param  Builder<ProcessAssignment>  $query
     * @return Builder<ProcessAssignment>
     */
    public function scopeOnObject(Builder $query, string $type, int $id): Builder
    {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    public function isInForce(): bool
    {
        $now = now();

        return $this->status === self::ACTIVE
            && $this->effective_from->lte($now)
            && ($this->effective_to === null || $this->effective_to->gt($now));
    }

    public function isLapsed(): bool
    {
        return $this->status === self::ACTIVE && $this->effective_to !== null && $this->effective_to->lte(now());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'subject_id' => 'integer',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
            'sod_check' => 'array',
            'closed_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }
}
