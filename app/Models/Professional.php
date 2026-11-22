<?php

namespace App\Models;

use App\Enums\ProfessionalType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'company_id',
    'slug',
    'type',
    'registration_body',
    'registration_number',
    'bio',
    'credentials_verified_at',
    'verified_by',
])]
class Professional extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ProfessionalType::class,
            'credentials_verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Professional $professional): void {
            if (blank($professional->slug)) {
                $professional->slug = static::uniqueSlug($professional);
            }
        });
    }

    private static function uniqueSlug(Professional $professional): string
    {
        $name = $professional->user?->name ?: ($professional->type?->value ?? 'professional').' professional';
        $base = Str::limit(Str::slug($name), 200, '');
        $base = $base !== '' ? $base : 'professional';

        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
