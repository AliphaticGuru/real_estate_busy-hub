<?php

namespace App\Policies;

use App\Models\Inquiry;
use App\Models\User;

class InquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Inquiry $inquiry): bool
    {
        return $this->resolve($user, $inquiry);
    }

    public function update(User $user, Inquiry $inquiry): bool
    {
        return $this->resolve($user, $inquiry);
    }

    public function delete(User $user, Inquiry $inquiry): bool
    {
        return $this->resolve($user, $inquiry);
    }

    private function resolve(User $user, Inquiry $inquiry): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($inquiry->professional_id !== null) {
            $professional = $inquiry->professional;

            if ($professional?->user_id === $user->id) {
                return true;
            }
        }

        if ($inquiry->listing_id !== null) {
            $listing = $inquiry->listing;

            if ($listing?->company_id !== null && $user->company_id === $listing->company_id) {
                return true;
            }
        }

        return false;
    }
}
