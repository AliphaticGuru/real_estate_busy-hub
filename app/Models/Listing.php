<?php

namespace App\Models;

use App\Enums\ListingPropertyType;
use App\Enums\ListingStatus;
use App\Enums\ListingVerificationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'company_id',
    'created_by',
    'title',
    'slug',
    'description',
    'property_type',
    'price',
    'city',
    'district',
    'address',
    'bedrooms',
    'bathrooms',
    'size_sqm',
    'status',
    'verification_status',
    'verified_at',
    'verified_by',
])]
class Listing extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'property_type' => ListingPropertyType::class,
            'price' => 'integer',
            'status' => ListingStatus::class,
            'verification_status' => ListingVerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class);
    }

    public function verificationChecks(): HasMany
    {
        return $this->hasMany(VerificationCheck::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected static function booted(): void
    {
        static::saved(function (Listing $listing): void {
            if (! $listing->wasChanged('verification_status')) {
                return;
            }

            AuditLog::create([
                'user_id' => $listing->verified_by,
                'action' => 'listing.verification_status.updated',
                'auditable_type' => 'listing',
                'auditable_id' => $listing->id,
                'old_values' => [
                    'verification_status' => $listing->getOriginal('verification_status')?->value,
                ],
                'new_values' => [
                    'verification_status' => $listing->verification_status?->value,
                ],
            ]);
        });
    }
}
