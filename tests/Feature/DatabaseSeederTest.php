<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_is_seeded(): void
    {
        $this->seed(RoleSeeder::class);

        $this->assertSame(count(RoleName::cases()), Role::count());

        foreach (RoleName::cases() as $role) {
            $this->assertDatabaseHas('roles', [
                'name' => $role->value,
                'label' => $role->label(),
            ]);
        }
    }

    public function test_role_seeder_is_idempotent(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertSame(count(RoleName::cases()), Role::count());
    }

    public function test_demo_accounts_are_seeded_with_the_expected_roles(): void
    {
        $this->seed();

        $expectations = [
            'admin@busyhub.test' => RoleName::SuperAdmin,
            'company@busyhub.test' => RoleName::Company,
            'professional@busyhub.test' => RoleName::Professional,
            'verifier@busyhub.test' => RoleName::Verifier,
            'customer@busyhub.test' => RoleName::Customer,
        ];

        foreach ($expectations as $email => $role) {
            $user = User::where('email', $email)->first();

            $this->assertNotNull($user, "Missing seeded user [{$email}].");
            $this->assertTrue($user->email_verified_at !== null);
            $this->assertTrue($user->hasRole($role));
        }
    }
}
