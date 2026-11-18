<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_can_view_the_verification_dashboard(): void
    {
        $verifier = User::factory()->verifier()->create();
        $listing = Listing::factory()->create([
            'verification_status' => 'in_review',
        ]);

        $this->actingAs($verifier)
            ->get(route('verify.index'))
            ->assertOk()
            ->assertSee($listing->title);
    }

    public function test_customer_cannot_view_the_verification_dashboard(): void
    {
        $customer = User::factory()->customer()->create();
        Listing::factory()->create();

        $this->actingAs($customer)
            ->get(route('verify.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_view_the_verification_dashboard(): void
    {
        $this->get(route('verify.index'))->assertRedirect(route('login'));
    }

    public function test_verifier_can_submit_a_passed_check_and_verify_the_listing(): void
    {
        $verifier = User::factory()->verifier()->create();
        $listing = Listing::factory()->create([
            'verification_status' => 'pending',
        ]);

        $this->actingAs($verifier)
            ->post(route('verify.checks.update', $listing->id), [
                'check_key' => 'survey_plan',
                'status' => 'passed',
            ])
            ->assertRedirect(route('verify.index'));

        $listing->refresh();
        $this->assertSame('verified', $listing->verification_status?->value);
        $this->assertNotNull($listing->verified_at);
        $this->assertSame($verifier->id, $listing->verified_by);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'listing',
            'auditable_id' => $listing->id,
            'action' => 'listing.verification_status.updated',
        ]);
    }

    public function test_verifier_can_submit_a_failed_check_and_reject_the_listing(): void
    {
        $verifier = User::factory()->verifier()->create();
        $listing = Listing::factory()->create([
            'verification_status' => 'in_review',
        ]);

        $this->actingAs($verifier)
            ->post(route('verify.checks.update', $listing->id), [
                'check_key' => 'title_document',
                'status' => 'failed',
            ])
            ->assertRedirect(route('verify.index'));

        $listing->refresh();
        $this->assertSame('rejected', $listing->verification_status?->value);
    }

    public function test_check_validation_rejects_unknown_check_keys(): void
    {
        $verifier = User::factory()->verifier()->create();

        $this->actingAs($verifier)
            ->post(route('verify.checks.update', Listing::factory()->create()->id), [
                'check_key' => 'not_a_real_check',
                'status' => 'passed',
            ])
            ->assertSessionHasErrors('check_key');
    }
}
