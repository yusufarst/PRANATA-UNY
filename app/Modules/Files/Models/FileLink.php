<?php

namespace App\Modules\Files\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A file tied to one row of an owning module, named by its table (DATA_MODEL section 12). The file, owner and purpose never
 * change; the link is released once and stays as history. A link of a sealed owner type is never released.
 *
 * @property int $id
 * @property int $file_id
 * @property string $owner_type
 * @property int $owner_id
 * @property string $purpose
 * @property string|null $label
 * @property int|null $linked_by
 * @property Carbon $linked_at
 * @property int|null $unlinked_by
 * @property Carbon|null $unlinked_at
 */
class FileLink extends Model
{
    public $timestamps = false;

    protected $table = 'file_links';

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<FileObject, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileObject::class, 'file_id');
    }

    /**
     * @param  Builder<FileLink>  $query
     * @return Builder<FileLink>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('unlinked_at');
    }

    /**
     * @param  Builder<FileLink>  $query
     * @return Builder<FileLink>
     */
    public function scopeOwnedBy(Builder $query, string $ownerType, int $ownerId): Builder
    {
        return $query->where('owner_type', $ownerType)->where('owner_id', $ownerId);
    }

    public function isActive(): bool
    {
        return $this->unlinked_at === null;
    }

    protected function casts(): array
    {
        return [
            'file_id' => 'integer',
            'owner_id' => 'integer',
            'linked_by' => 'integer',
            'linked_at' => 'datetime',
            'unlinked_by' => 'integer',
            'unlinked_at' => 'datetime',
        ];
    }
}
