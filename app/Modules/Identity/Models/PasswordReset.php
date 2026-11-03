<?php

namespace App\Modules\Identity\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A one-time secret an administrator issued: an activation code or a temporary password (DATA_MODEL section 5).
 *
 * @property int $id
 * @property int $user_id
 * @property string $channel
 * @property int|null $requested_by
 * @property int|null $approved_by
 * @property string|null $identity_check_method
 * @property string|null $identity_check_note
 * @property string|null $temp_secret_hash
 * @property string $status
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property Carbon|null $created_at
 */
class PasswordReset extends Model
{
    public const ACTIVATION = 'ACTIVATION';

    public const ADMIN_ASSISTED = 'ADMIN_ASSISTED';

    public const PENDING_APPROVAL = 'PENDING_APPROVAL';

    public const ISSUED = 'ISSUED';

    public const USED = 'USED';

    public const EXPIRED = 'EXPIRED';

    public const REVOKED = 'REVOKED';

    /**
     * How the administrator checked who asked (SECURITY_MODEL section 3.1, RB-08).
     */
    public const IDENTITY_CHECKS = ['IN_PERSON', 'STAFF_CARD', 'VENDOR_LETTER'];

    public const UPDATED_AT = null;

    protected $table = 'identity_password_resets';

    protected $guarded = ['id'];

    protected $hidden = ['temp_secret_hash'];

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
     * Whether the secret, or the request for it, is still open at this moment.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, [self::PENDING_APPROVAL, self::ISSUED], true) && $this->expires_at->isFuture();
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
