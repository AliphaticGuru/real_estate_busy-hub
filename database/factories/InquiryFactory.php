<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => InquiryType::Consultation->value,
            'listing_id' => Listing::factory(),
            'professional_id' => Professional::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'message' => fake()->paragraph(),
            'preferred_date' => null,
            'status' => InquiryStatus::New->value,
            'user_id' => null,
        ];
    }
}
