<?php

namespace App\Models;

use App\Enums\CheckKey;
use App\Enums\CheckStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['listing_id', 'check_key', 'status', 'notes', 'checked_by', 'checked_at'])]
class VerificationCheck extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'check_key' => CheckKey::class,
            'status' => CheckStatus::class,
            'checked_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected static function booted(): void
    {
        static::updated(function (VerificationCheck $check): void {
            if (! $check->wasChanged('status') && ! $check->wasChanged('checked_at')) {
                return;
            }

            AuditLog::create([
                'user_id' => $check->checked_by,
                'action' => 'verification_check.updated',
                'auditable_type' => 'verification_check',
                'auditable_id' => $check->id,
                'old_values' => [
                    'status' => $check->getOriginal('status')?->value,
                    'checked_at' => $check->getOriginal('checked_at'),
                ],
                'new_values' => [
                    'status' => $check->status?->value,
                    'checked_at' => $check->checked_at,
                ],
            ]);
        });
    }
}
