<?php

namespace Database\Factories;

use App\Enums\ListingPropertyType;
use App\Enums\ListingStatus;
use App\Enums\ListingVerificationStatus;
use App\Models\Company;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'property_type' => ListingPropertyType::House->value,
            'price' => fake()->numberBetween(1_000_000, 1_000_000_000),
            'city' => 'Abuja',
            'district' => 'Gwarinpa',
            'address' => fake()->streetAddress(),
            'bedrooms' => 2,
            'bathrooms' => 1,
            'size_sqm' => 120,
            'status' => ListingStatus::Draft->value,
            'verification_status' => ListingVerificationStatus::Pending->value,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ListingStatus::Published->value,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => ListingVerificationStatus::Verified->value,
            'verified_at' => now(),
        ]);
    }

    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => ListingVerificationStatus::InReview->value,
            'verified_at' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => ListingVerificationStatus::Rejected->value,
            'verified_at' => null,
        ]);
    }
}
