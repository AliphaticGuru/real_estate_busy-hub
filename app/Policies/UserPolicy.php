<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Super admins are the only users allowed to browse the user directory.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Super admins can view any user; everyone else only their own account.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->is($model);
    }

    /**
     * Only super admins can create users (public registration uses its own flow).
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Super admins can update any user; everyone else only their own account.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->is($model);
    }

    /**
     * Super admins can delete other users, but nobody can delete themselves.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isSuperAdmin() && ! $user->is($model);
    }

    /**
     * Only super admins may restore removed accounts.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Only super admins may permanently delete accounts.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperAdmin() && ! $user->is($model);
    }
}
