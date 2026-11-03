<?php

namespace App\Modules\Identity\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A request to remove the TOTP of an account, which a second administrator approves (TASK-012 TAC-03).
 *
 * @property int $id
 * @property int $user_id
 * @property int $requested_by
 * @property int|null $approved_by
 * @property string $identity_check_method
 * @property string|null $identity_check_note
 * @property string $status
 * @property Carbon $expires_at
 * @property Carbon|null $decided_at
 * @property Carbon|null $created_at
 */
class TwoFactorReset extends Model
{
    public const PENDING_APPROVAL = 'PENDING_APPROVAL';

    public const APPROVED = 'APPROVED';

    public const EXPIRED = 'EXPIRED';

    public const REVOKED = 'REVOKED';

    public const UPDATED_AT = null;

    protected $table = 'identity_two_factor_resets';

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Whether the request still waits for its second administrator at this moment.
     */
    public function isOpen(): bool
    {
        return $this->status === self::PENDING_APPROVAL && $this->expires_at->isFuture();
    }

    /**
     * The request of the account that still waits, if any.
     */
    public static function pendingFor(User $user): ?self
    {
        return self::query()
            ->with('requester')
            ->where('user_id', $user->getKey())
            ->where('status', self::PENDING_APPROVAL)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }
}
