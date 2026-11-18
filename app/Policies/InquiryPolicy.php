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
        // Super admin can view any inquiry
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Professionals can only view their own inquiries (linked via professional_id)
        if ($inquiry->professional_id !== null) {
            $professional = $inquiry->professional;

            if ($professional?->user_id === $user->id) {
                return true;
            }
        }

        // Company users can only view inquiries for their own listings
        if ($inquiry->listing_id !== null) {
            $listing = $inquiry->listing;

            if ($listing?->company_id !== null && $user->company_id === $listing->company_id) {
                return true;
            }
        }

        // Customers can only view their own inquiries
        if ($inquiry->user_id !== null && $inquiry->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function update(User $user, Inquiry $inquiry): bool
    {
        // Only super_admin can change verification status
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Company users can only update inquiries for their own listings
        if ($inquiry->listing_id !== null) {
            $listing = $inquiry->listing;

            if ($listing?->company_id !== null && $user->company_id === $listing->company_id) {
                return true;
            }
        }

        // Professionals can only update their own inquiries
        if ($inquiry->professional_id !== null) {
            $professional = $inquiry->professional;

            if ($professional?->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, Inquiry $inquiry): bool
    {
        return $this->update($user, $inquiry);
    }
}