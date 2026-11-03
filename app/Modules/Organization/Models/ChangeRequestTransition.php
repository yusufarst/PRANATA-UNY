<?php

namespace App\Modules\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One transition TR-281 to TR-288 of a change request: append-only, until the generic workflow_transitions exist.
 *
 * @property int $id
 * @property int $change_request_id
 * @property string $transition_code
 * @property string|null $from_state
 * @property string $to_state
 * @property int|null $actor_user_id
 * @property Carbon $acted_at
 * @property string|null $reason
 * @property bool $is_simulation
 * @property array<string, mixed>|null $metadata
 * @property string|null $request_id
 */
class ChangeRequestTransition extends Model
{
    public $timestamps = false;

    protected $table = 'org_change_request_transitions';

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
            'is_simulation' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
