<?php

namespace Database\Factories;

use App\Enums\ProfessionalType;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Professional>
 */
class ProfessionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => null,
            'type' => ProfessionalType::Lawyer->value,
            'registration_body' => 'NBA',
            'registration_number' => (string) fake()->numberBetween(100000000, 999999999),
            'bio' => fake()->paragraph(),
            'credentials_verified_at' => null,
            'verified_by' => null,
        ];
    }

    /**
     * Mark the professional's credentials as verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'credentials_verified_at' => now(),
        ]);
    }
}
