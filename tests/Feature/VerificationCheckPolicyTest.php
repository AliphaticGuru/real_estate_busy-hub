<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationCheckPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_can_update_a_verification_check(): void
    {
        $verifier = User::factory()->verifier()->create();
        $check = VerificationCheck::factory()->create();

        $this->assertTrue($verifier->can('update', $check));
    }

    public function test_super_admin_can_update_a_verification_check(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $check = VerificationCheck::factory()->create();

        $this->assertTrue($superAdmin->can('update', $check));
    }

    public function test_customer_cannot_update_a_verification_check(): void
    {
        $customer = User::factory()->customer()->create();
        $check = VerificationCheck::factory()->create();

        $this->assertFalse($customer->can('update', $check));
    }
}
