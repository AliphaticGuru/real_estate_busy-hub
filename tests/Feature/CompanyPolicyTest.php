<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_any_company(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertTrue($superAdmin->can('viewAny', Company::class));
    }

    public function test_company_staff_can_view_their_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company()->create();
        $user->company_id = $company->id;
        $user->save();

        $this->assertTrue($user->can('view', $company));
    }

    public function test_company_staff_cannot_view_another_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $userA = User::factory()->company()->create();
        $userA->company_id = $companyA->id;
        $userA->save();

        $this->assertFalse($userA->can('view', $companyB));
    }

    public function test_only_super_admin_can_create_companies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $companyUser = User::factory()->company()->create();

        $this->assertTrue($superAdmin->can('create', Company::class));
        $this->assertFalse($companyUser->can('create', Company::class));
    }

    public function test_only_super_admin_can_delete_companies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $company = Company::factory()->create();

        $this->assertTrue($superAdmin->can('delete', $company));
        $this->assertFalse(User::factory()->company()->create()->can('delete', $company));
    }
}
