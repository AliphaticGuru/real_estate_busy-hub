<?php

namespace Database\Factories;

use App\Enums\CheckKey;
use App\Enums\CheckStatus;
use App\Models\Listing;
use App\Models\VerificationCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerificationCheck>
 */
class VerificationCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'check_key' => CheckKey::TitleDocument->value,
            'status' => CheckStatus::Pending->value,
            'notes' => null,
            'checked_by' => null,
            'checked_at' => null,
        ];
    }
}
