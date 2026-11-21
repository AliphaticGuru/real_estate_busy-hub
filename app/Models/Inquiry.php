<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['type', 'listing_id', 'professional_id', 'name', 'phone', 'email', 'message', 'preferred_date', 'status'])]
class Inquiry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => InquiryType::class,
            'preferred_date' => 'datetime',
            'status' => InquiryStatus::class,
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the inquiry is viewable by the given user.
     */
    public function viewableBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Professional scoping: check professional first
        if ($this->professional_id !== null) {
            $professional = $this->professional;
            if ($professional?->user_id === $user->id) {
                return true;
            }
            // If professional_id is set but professional doesn't match,
            // fall through to listing check (company users may still have access)
        }

        // Company scoping: view via listing.company_id
        if ($this->listing_id !== null) {
            $listing = $this->listing;
            if ($listing?->company_id !== null && $user->company_id === $listing->company_id) {
                return true;
            }
        }

        // Customer scoping: view via user_id
        if ($this->user_id !== null && $this->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the inquiry status is updatable by the given user.
     */
    public function updatableBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->listing_id !== null) {
            $listing = $this->listing;
            if ($listing?->company_id !== null && $user->company_id === $listing->company_id) {
                return true;
            }
        }

        if ($this->professional_id !== null) {
            $professional = $this->professional;
            if ($professional?->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }
}
