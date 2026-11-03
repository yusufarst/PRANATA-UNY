<?php

namespace App\Modules\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A dated appointment of a person to a position, with its decree. It suggests assignments and signer names; it never grants
 * a permission (OD-28, TASK-013 TAC-03).
 *
 * @property int $id
 * @property int $user_id
 * @property int $position_id
 * @property int|null $org_unit_id
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 * @property string|null $decree_ref
 * @property string $status
 * @property int|null $change_request_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrgAppointment extends Model
{
    public const ACTIVE = 'ACTIVE';

    public const ENDED = 'ENDED';

    protected $table = 'org_appointments';

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<OrgPosition, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(OrgPosition::class);
    }

    /**
     * @return BelongsTo<OrgUnit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'org_unit_id');
    }

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }
}
