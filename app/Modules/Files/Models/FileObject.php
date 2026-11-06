<?php

namespace App\Modules\Files\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The identity of stored bytes (DATA_MODEL section 12): where they are, what they were called, and the size and SHA-256 the
 * server computed from the bytes it stored. The identity never changes; only the classification (upwards) and the scan
 * status do. Different content is always a new file object.
 *
 * @property int $id
 * @property string $public_id
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $sha256
 * @property string $classification
 * @property string $scan_status
 * @property int|null $uploaded_by
 * @property Carbon $uploaded_at
 */
class FileObject extends Model
{
    use HasUlids;

    public const INTERNAL = 'INTERNAL';

    public const CONFIDENTIAL = 'CONFIDENTIAL';

    public const RESTRICTED = 'RESTRICTED';

    public const NOT_SCANNED = 'NOT_SCANNED';

    public const CLEAN = 'CLEAN';

    public const INFECTED = 'INFECTED';

    public const ERROR = 'ERROR';

    /**
     * The only types a browser may show inline (SECURITY_MODEL section 6); everything else downloads as an attachment.
     */
    public const PREVIEWABLE = ['application/pdf', 'image/png', 'image/jpeg'];

    public $timestamps = false;

    protected $table = 'file_objects';

    protected $guarded = ['id'];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /**
     * @return HasMany<FileLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(FileLink::class, 'file_id');
    }

    /**
     * Whether a download of this file is recorded in the audit trail (SECURITY_MODEL section 6).
     */
    public function isConfidential(): bool
    {
        return $this->classification !== self::INTERNAL;
    }

    public function isPreviewable(): bool
    {
        return in_array($this->mime_type, self::PREVIEWABLE, true);
    }

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'uploaded_by' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }
}
