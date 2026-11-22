<?php

namespace Tests\Feature;

use App\Enums\ProfessionalType;
use App\Models\Company;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use App\Models\User;
use App\Notifications\InquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfessionalInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_the_consultation_form(): void
    {
        $professional = $this->verifiedProfessional('Ada Okafor');

        $this->get(route('professionals.inquiries.create', $professional->slug))
            ->assertOk()
            ->assertSee('Ada Okafor')
            ->assertSee('Request consultation')
            ->assertSee('name="_token"', false)
            ->assertDontSee('name="type"', false);
    }

    public function test_unverified_professional_cannot_receive_consultation_requests(): void
    {
        $professional = $this->verifiedProfessional('Ngozi Eze', verified: false);

        $this->get(route('professionals.inquiries.create', $professional->slug))->assertNotFound();

        $this->post(route('professionals.inquiries.store', $professional->slug), $this->payload())
            ->assertNotFound();

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_guest_submission_creates_a_consultation_inquiry_for_the_professional(): void
    {
        Notification::fake();

        $professional = $this->verifiedProfessional('Ada Okafor', email: 'ada@example.com');

        $this->post(route('professionals.inquiries.store', $professional->slug), $this->payload())
            ->assertRedirect(route('professionals.show', $professional->slug))
            ->assertSessionHas('status', 'Thank you! Your inquiry has been sent.');

        $this->assertDatabaseHas('inquiries', [
            'type' => 'consultation',
            'listing_id' => null,
            'professional_id' => $professional->id,
            'name' => 'Jane Submitter',
            'user_id' => null,
        ]);

        Notification::assertSentOnDemandTimes(InquiryReceived::class, 1);
        Notification::assertSentOnDemand(
            InquiryReceived::class,
            fn (InquiryReceived $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === 'ada@example.com',
        );
    }

    public function test_customer_submission_records_their_user_and_ignores_posted_ids_and_type(): void
    {
        $customer = User::factory()->customer()->create();
        $addressed = $this->verifiedProfessional('Ada Okafor');
        $other = $this->verifiedProfessional('Tunde Bello');

        $company = Company::factory()->create();
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        $this->actingAs($customer)
            ->post(route('professionals.inquiries.store', $addressed->slug), $this->payload([
                'type' => 'inspection',
                'user_id' => $other->user_id,
                'professional_id' => $other->id,
                'listing_id' => $listing->id,
            ]))
            ->assertRedirect(route('professionals.show', $addressed->slug));

        $inquiry = Inquiry::query()->sole();

        $this->assertSame($customer->id, $inquiry->user_id);
        $this->assertSame($addressed->id, $inquiry->professional_id);
        $this->assertNull($inquiry->listing_id);
        $this->assertSame('consultation', $inquiry->type->value);
    }

    public function test_notification_goes_only_to_the_addressed_professional(): void
    {
        Notification::fake();

        $addressed = $this->verifiedProfessional('Ada Okafor', email: 'ada@example.com');
        $this->verifiedProfessional('Tunde Bello', email: 'tunde@example.com');

        $company = Company::factory()->create(['email' => 'company@example.com']);
        Listing::factory()->published()->create(['company_id' => $company->id]);

        $this->post(route('professionals.inquiries.store', $addressed->slug), $this->payload())
            ->assertRedirect(route('professionals.show', $addressed->slug));

        Notification::assertSentOnDemandTimes(InquiryReceived::class, 1);
        Notification::assertSentOnDemand(
            InquiryReceived::class,
            fn (InquiryReceived $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === 'ada@example.com',
        );
    }

    public function test_professional_notification_names_the_professional_instead_of_a_listing(): void
    {
        Notification::fake();

        $professional = $this->verifiedProfessional('Ada Okafor', email: 'ada@example.com');

        $this->post(route('professionals.inquiries.store', $professional->slug), $this->payload([
            'phone' => '+2348012345678',
            'email' => 'jane.submitter@example.com',
        ]))->assertRedirect(route('professionals.show', $professional->slug));

        Notification::assertSentOnDemand(
            InquiryReceived::class,
            function (InquiryReceived $notification, array $channels, $notifiable): bool {
                $html = $notification->toMail($notifiable)->render();

                $this->assertStringContainsString('Professional: Ada Okafor', $html);
                $this->assertStringNotContainsString('Listing:', $html);
                $this->assertStringNotContainsString('+2348012345678', $html);
                $this->assertStringNotContainsString('jane.submitter@example.com', $html);

                return true;
            },
        );
    }

    public function test_addressed_professional_sees_the_inquiry_on_the_index_and_dashboard_only(): void
    {
        $addressed = $this->verifiedProfessional('Ada Okafor');
        $other = $this->verifiedProfessional('Tunde Bello');

        $inquiry = Inquiry::factory()->create([
            'listing_id' => null,
            'professional_id' => $addressed->id,
            'name' => 'Jane Submitter',
            'status' => 'new',
        ]);

        $this->actingAs($addressed->user)
            ->get(route('inquiries.index'))
            ->assertOk()
            ->assertSee('Jane Submitter');

        $this->actingAs($addressed->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Jane Submitter')
            ->assertSee(route('inquiries.show', $inquiry), false);

        $this->actingAs($other->user)
            ->get(route('inquiries.index'))
            ->assertOk()
            ->assertDontSee('Jane Submitter');

        $this->actingAs($other->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Jane Submitter');

        $company = Company::factory()->create();
        $companyUser = User::factory()->company()->create(['company_id' => $company->id]);

        $this->actingAs($companyUser)
            ->get(route('inquiries.index'))
            ->assertOk()
            ->assertDontSee('Jane Submitter');

        $verifier = User::factory()->verifier()->create();

        $this->actingAs($verifier)
            ->get(route('inquiries.index'))
            ->assertForbidden();
    }

    public function test_honeypot_submission_stores_and_sends_nothing(): void
    {
        Notification::fake();

        $professional = $this->verifiedProfessional('Ada Okafor');

        $this->post(route('professionals.inquiries.store', $professional->slug), [
            'website' => 'https://spam.example',
        ])
            ->assertRedirect(route('professionals.show', $professional->slug))
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('inquiries', 0);
        Notification::assertNothingSent();
    }

    public function test_sixth_consultation_request_from_the_same_ip_is_throttled(): void
    {
        Notification::fake();

        $professional = $this->verifiedProfessional('Ada Okafor');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('professionals.inquiries.store', $professional->slug), $this->payload([
                'name' => "Jane {$attempt}",
            ]))->assertRedirect(route('professionals.show', $professional->slug));
        }

        $this->post(route('professionals.inquiries.store', $professional->slug), $this->payload([
            'name' => 'Jane Sixth',
        ]))->assertStatus(429);

        $this->assertDatabaseCount('inquiries', 5);
    }

    public function test_consultation_requires_a_message(): void
    {
        $professional = $this->verifiedProfessional('Ada Okafor');

        $this->from(route('professionals.inquiries.create', $professional->slug))
            ->post(route('professionals.inquiries.store', $professional->slug), $this->payload([
                'message' => '',
            ]))
            ->assertSessionHasErrors('message');

        $this->assertDatabaseCount('inquiries', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Submitter',
            'phone' => '+2348099999999',
            'email' => 'jane.submitter@example.com',
            'message' => 'I would like to discuss a property transaction.',
            'preferred_date' => '2026-12-01',
        ], $overrides);
    }

    private function verifiedProfessional(
        string $name,
        bool $verified = true,
        ?string $email = null,
    ): Professional {
        $user = User::factory()->professional()->create(array_filter([
            'name' => $name,
            'email' => $email,
        ]));

        $factory = Professional::factory()->state([
            'user_id' => $user->id,
            'type' => ProfessionalType::Lawyer->value,
        ]);

        if ($verified) {
            $factory = $factory->verified();
        }

        return $factory->create();
    }
}
