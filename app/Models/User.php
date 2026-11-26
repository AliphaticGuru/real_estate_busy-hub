<?php

namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'notify_inquiries'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * New users start out as customers; other roles are assigned explicitly.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (is_null($user->role_id)) {
                $user->role_id = Role::forName(RoleName::Customer)->id;
            }
        });
    }

    /**
     * The role assigned to the user.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The company the user belongs to (company staff).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The inquiries for the user (customers only).
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'user_id');
    }

    /**
     * Assign a role to the user, persisting it when the user exists.
     */
    public function assignRole(RoleName|Role $role): static
    {
        $this->role()->associate($role instanceof Role ? $role : Role::forName($role));

        if ($this->exists) {
            $this->save();
        }

        return $this;
    }

    /**
     * Determine whether the user has one of the given roles.
     */
    public function hasRole(RoleName ...$roles): bool
    {
        $current = $this->role?->name;

        return $current instanceof RoleName && in_array($current, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleName::SuperAdmin);
    }

    public function isCompany(): bool
    {
        return $this->hasRole(RoleName::Company);
    }

    public function isProfessional(): bool
    {
        return $this->hasRole(RoleName::Professional);
    }

    public function isVerifier(): bool
    {
        return $this->hasRole(RoleName::Verifier);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(RoleName::Customer);
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
            'notify_inquiries' => 'bool',
        ];
    }
}
