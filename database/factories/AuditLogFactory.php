<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'listing.created',
            'auditable_type' => 'listing',
            'auditable_id' => 1,
            'old_values' => null,
            'new_values' => ['listing' => ['title' => fake()->sentence()]],
            'ip_address' => fake()->ipv4(),
        ];
    }
}
