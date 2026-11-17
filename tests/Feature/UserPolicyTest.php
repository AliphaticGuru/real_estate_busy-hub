<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_the_user_directory(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->assertTrue($admin->can('viewAny', User::class));
    }

    public function test_non_admin_roles_cannot_view_the_user_directory(): void
    {
        $users = [
            User::factory()->company()->create(),
            User::factory()->professional()->create(),
            User::factory()->verifier()->create(),
            User::factory()->customer()->create(),
        ];

        foreach ($users as $user) {
            $this->assertFalse($user->can('viewAny', User::class));
        }
    }

    public function test_users_can_view_and_update_their_own_account(): void
    {
        $user = User::factory()->company()->create();

        $this->assertTrue($user->can('view', $user));
        $this->assertTrue($user->can('update', $user));
    }

    public function test_users_cannot_view_or_update_other_accounts(): void
    {
        $user = User::factory()->company()->create();
        $other = User::factory()->customer()->create();

        $this->assertFalse($user->can('view', $other));
        $this->assertFalse($user->can('update', $other));
    }

    public function test_super_admin_can_view_and_update_other_accounts(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $other = User::factory()->customer()->create();

        $this->assertTrue($admin->can('view', $other));
        $this->assertTrue($admin->can('update', $other));
    }

    public function test_only_super_admin_can_create_users(): void
    {
        $this->assertTrue(User::factory()->superAdmin()->create()->can('create', User::class));
        $this->assertFalse(User::factory()->verifier()->create()->can('create', User::class));
    }

    public function test_super_admin_can_delete_other_users(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $other = User::factory()->customer()->create();

        $this->assertTrue($admin->can('delete', $other));
    }

    public function test_non_admins_cannot_delete_accounts(): void
    {
        $user = User::factory()->company()->create();
        $other = User::factory()->customer()->create();

        $this->assertFalse($user->can('delete', $other));
    }

    public function test_nobody_can_delete_their_own_account_through_the_policy(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->assertFalse($admin->can('delete', $admin));
        $this->assertFalse($admin->can('forceDelete', $admin));
    }
}
