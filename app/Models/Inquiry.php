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
}