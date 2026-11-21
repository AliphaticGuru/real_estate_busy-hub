<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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
     * Scope a query to only inquiries the given user is allowed to view.
     */
    public function scopeWhereViewableBy(Builder $query, ?User $user): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user) {
            $query->where(function (Builder $query) use ($user) {
                $query->whereHas('professional', fn (Builder $professional) => $professional->where('user_id', $user->id));
            })->orWhere(function (Builder $query) use ($user) {
                $query->whereHas('listing', fn (Builder $listing) => $listing->where('company_id', $user->company_id));
            })->orWhere(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            });
        });
    }

    /**
     * Determine if the inquiry is viewable by the given user.
     */
    public function viewableBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return static::query()
            ->whereKey($this->getKey())
            ->whereViewableBy($user)
            ->exists();
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
