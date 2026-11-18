<?php

namespace App\Policies;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isVerifier() || $user->isCompany();
    }

    public function view(User $user, Listing $listing): bool
    {
        if ($user->isSuperAdmin() || $user->isVerifier()) {
            return true;
        }

        if ($user->isCompany()) {
            return $user->company_id === $listing->company_id;
        }

        if ($user->isCustomer()) {
            return $listing->status === ListingStatus::Published;
        }

        return false;
    }

    public function update(User $user, Listing $listing): bool
    {
        if ($user->isSuperAdmin() || $user->isVerifier()) {
            return true;
        }

        return $user->isCompany() && $user->company_id === $listing->company_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        if ($user->isSuperAdmin() || $user->isVerifier()) {
            return true;
        }

        return $user->isCompany() && $user->company_id === $listing->company_id;
    }

    public function updateVerificationStatus(User $user, Listing $listing): bool
    {
        return $user->isSuperAdmin() || $user->isVerifier();
    }
}
