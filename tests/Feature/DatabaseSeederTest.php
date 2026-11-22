<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Company;
use App\Models\Listing;
use App\Models\Professional;
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

    public function test_demo_seeders_are_skipped_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');

        $this->seed();

        $this->assertSame(0, User::count());
        $this->assertSame(0, Company::count());
        $this->assertSame(0, Professional::count());
        $this->assertSame(0, Listing::count());
    }

    public function test_demo_seeders_run_in_the_local_environment(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'local');

        $this->seed();

        $this->assertNotNull(User::where('email', 'admin@busyhub.test')->first());
        $this->assertNotNull(Professional::where('slug', 'ada-okafor')->first());
        $this->assertNotNull(Listing::where('slug', 'acme-grand-villa')->first());
    }
}
