<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppCreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_command_refuses_to_run_non_interactively_without_force(): void
    {
        $this->artisan('app:create-admin', ['--no-interaction' => true])
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'admin@busyhub.test']);
    }

    public function test_admin_command_creates_a_super_admin_with_force(): void
    {
        $this->artisan('app:create-admin', ['--force' => true])
            ->assertExitCode(0);

        $user = User::where('email', 'admin@busyhub.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(RoleName::SuperAdmin));
    }
}
