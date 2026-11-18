<?php

namespace Database\Factories;

use App\Enums\CompanyStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'logo_path' => 'companies/logo.png',
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'whatsapp_number' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => 'Abuja',
            'state' => 'Federal Capital Territory',
            'status' => CompanyStatus::Pending->value,
        ];
    }
}
