<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An official position or function as reference data, such as KABAG_ALP (DATA_MODEL section 6).
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $org_unit_id
 * @property string|null $description
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrgPosition extends Model
{
    public const ACTIVE = 'ACTIVE';

    public const INACTIVE = 'INACTIVE';

    protected $table = 'org_positions';

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<OrgUnit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'org_unit_id');
    }
}
