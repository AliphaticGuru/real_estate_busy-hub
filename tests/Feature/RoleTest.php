<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_are_assigned_the_customer_role_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->isCustomer());
        $this->assertSame(RoleName::Customer, $user->role->name);
    }

    public function test_roles_can_be_assigned_to_users(): void
    {
        $user = User::factory()->company()->create();

        $this->assertTrue($user->isCompany());
        $this->assertFalse($user->isCustomer());
    }

    public function test_role_helpers_report_the_correct_role(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasRole(RoleName::SuperAdmin, RoleName::Verifier));
        $this->assertFalse($user->hasRole(RoleName::Company, RoleName::Customer));
    }

    public function test_user_roles_are_cast_to_the_role_enum(): void
    {
        $user = User::factory()->professional()->create();

        $this->assertInstanceOf(RoleName::class, $user->role->name);
        $this->assertSame('professional', $user->role->name->value);
    }

    public function test_role_labels_are_human_readable(): void
    {
        $this->assertSame('Super Admin', RoleName::SuperAdmin->label());
        $this->assertSame('Company', RoleName::Company->label());
        $this->assertSame('Professional', RoleName::Professional->label());
        $this->assertSame('Verifier', RoleName::Verifier->label());
        $this->assertSame('Customer', RoleName::Customer->label());
    }

    public function test_role_has_many_users(): void
    {
        $role = Role::forName(RoleName::Verifier);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue($role->is($user->fresh()->role));
        $this->assertSame(1, $role->users()->count());
    }

    public function test_role_for_name_reuses_existing_records(): void
    {
        $first = Role::forName(RoleName::Company);
        $second = Role::forName(RoleName::Company);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Role::where('name', RoleName::Company->value)->count());
    }
}
