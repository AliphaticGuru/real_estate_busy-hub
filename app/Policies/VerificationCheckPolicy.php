<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VerificationCheck;

class VerificationCheckPolicy
{
    public function view(User $user, VerificationCheck $check): bool
    {
        return $user->isSuperAdmin() || $user->isVerifier();
    }

    public function update(User $user, VerificationCheck $check): bool
    {
        return $user->isSuperAdmin() || $user->isVerifier();
    }

    public function delete(User $user, VerificationCheck $check): bool
    {
        return $user->isSuperAdmin() || $user->isVerifier();
    }
}
