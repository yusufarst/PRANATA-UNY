<?php

namespace App\Modules\Organization\Models;

use App\Modules\Platform\Support\HasLockVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An organization unit of any type (OD-04): the type is a code, never a fixed list of faculties. path is the ltree of the
 * public ids from the root down to this unit.
 *
 * @property int $id
 * @property string $public_id
 * @property string $code
 * @property string $name
 * @property string|null $short_name
 * @property string $unit_type
 * @property int|null $parent_id
 * @property string $path
 * @property int $depth
 * @property string $status
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 * @property array<int, string>|null $legacy_codes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrgUnit extends Model
{
    use HasLockVersion, HasUlids;

    public const ACTIVE = 'ACTIVE';

    public const INACTIVE = 'INACTIVE';

    protected $table = 'org_units';

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

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * @return BelongsTo<OrgUnit, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<OrgUnitHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(OrgUnitHistory::class);
    }

    /**
     * This unit and every unit below it.
     *
     * @param  Builder<OrgUnit>  $query
     * @return Builder<OrgUnit>
     */
    public function scopeWithinSubtreeOf(Builder $query, self $unit): Builder
    {
        return $query->whereRaw('path <@ ?::ltree', [$unit->path]);
    }

    /**
     * The fields a change request may set and the history compares.
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'unit_type' => $this->unit_type,
            'parent_public_id' => $this->parent?->public_id,
            'path' => $this->path,
            'status' => $this->status,
            'effective_from' => $this->effective_from->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
        ];
    }

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'legacy_codes' => 'array',
            'lock_version' => 'integer',
        ];
    }
}
