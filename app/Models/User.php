<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $public_id
 * @property string $role_group
 * @property string $username
 * @property string $name
 * @property string|null $email
 * @property string|null $staff_identifier
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string $status
 * @property bool $must_change_password
 * @property Carbon|null $password_changed_at
 * @property Carbon|null $last_login_at
 * @property Carbon|null $deactivated_at
 * @property string $locale
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $created_by
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable, TwoFactorAuthenticatable;

    public const SUPER_ADMIN = 'SUPER_ADMIN';

    public const INTERNAL = 'INTERNAL';

    public const VENDOR = 'VENDOR';

    public const ACTIVE = 'ACTIVE';

    public const SUSPENDED = 'SUSPENDED';

    public const LOCKED = 'LOCKED';

    public const DEACTIVATED = 'DEACTIVATED';

    /**
     * The id stays the bigint key; only public_id is a ULID.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /**
     * Whether the account may hold a session: only an ACTIVE one may, whatever cookie it brings (TASK-010 TAC-02).
     */
    public function mayHoldSession(): bool
    {
        return $this->status === self::ACTIVE;
    }

    /**
     * An account without an email has nothing to verify, so the verified routes stay open to it.
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email === null || $this->email_verified_at !== null;
    }

    /**
     * No verification mail for an account without an email.
     */
    public function sendEmailVerificationNotification(): void
    {
        if ($this->email !== null) {
            parent::sendEmailVerificationNotification();
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }
}
