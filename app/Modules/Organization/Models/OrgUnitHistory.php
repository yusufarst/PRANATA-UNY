<?php

namespace App\Modules\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One version step of a unit (INV-017/018): append-only, so a change never overwrites the version before it.
 *
 * @property int $id
 * @property int $org_unit_id
 * @property string $change_type
 * @property array<string, mixed>|null $before
 * @property array<string, mixed>|null $after
 * @property Carbon $effective_from
 * @property string|null $decision_ref
 * @property int|null $change_request_id
 * @property bool $is_simulation
 * @property int|null $changed_by
 * @property Carbon $changed_at
 */
class OrgUnitHistory extends Model
{
    public const CREATE = 'CREATE';

    public const RENAME = 'RENAME';

    public const MOVE = 'MOVE';

    public const ACTIVATE = 'ACTIVATE';

    public const DEACTIVATE = 'DEACTIVATE';

    public const CORRECT = 'CORRECT';

    public $timestamps = false;

    protected $table = 'org_unit_history';

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * @return BelongsTo<ChangeRequest, $this>
     */
    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'effective_from' => 'date',
            'is_simulation' => 'boolean',
            'changed_at' => 'datetime',
        ];
    }
}
