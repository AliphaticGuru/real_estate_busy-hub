<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use App\Models\User;
use App\Notifications\InquiryReceived;
use App\Services\InquiryNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class InquiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_inquiry_notifies_the_listing_company_only(): void
    {
        Notification::fake();

        $company = Company::factory()->create(['email' => 'company@example.com']);
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);
        Company::factory()->create(['email' => 'other@example.com']);

        $this->post(route('inquiries.store', $listing->slug), $this->payload())
            ->assertRedirect(route('listings.show', $listing->slug))
            ->assertSessionHas('status');

        Notification::assertSentOnDemandTimes(InquiryReceived::class, 1);
        Notification::assertSentOnDemand(
            InquiryReceived::class,
            fn (InquiryReceived $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === 'company@example.com',
        );

        $this->assertDatabaseHas('inquiries', [
            'listing_id' => $listing->id,
            'name' => 'Jane Submitter',
        ]);
    }

    public function test_professional_inquiry_notifies_that_professional_only(): void
    {
        Notification::fake();

        $professionalUser = User::factory()->professional()->create(['email' => 'professional@example.com']);
        $professional = Professional::factory()->create(['user_id' => $professionalUser->id]);

        $company = Company::factory()->create(['email' => 'company@example.com']);
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        $inquiry = Inquiry::factory()->create([
            'listing_id' => $listing->id,
            'professional_id' => $professional->id,
        ]);

        app(InquiryNotifier::class)->notify($inquiry);

        Notification::assertSentOnDemandTimes(InquiryReceived::class, 1);
        Notification::assertSentOnDemand(
            InquiryReceived::class,
            fn (InquiryReceived $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === 'professional@example.com',
        );
    }

    public function test_company_owner_opt_out_skips_inquiry_notification(): void
    {
        Notification::fake();

        $company = Company::factory()->create(['email' => 'company@example.com']);
        User::factory()->company()->create([
            'company_id' => $company->id,
            'notify_inquiries' => false,
        ]);
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        $this->post(route('inquiries.store', $listing->slug), $this->payload())
            ->assertRedirect(route('listings.show', $listing->slug));

        Notification::assertNothingSent();
    }

    public function test_company_owner_opt_in_still_notifies_the_company_email(): void
    {
        Notification::fake();

        $company = Company::factory()->create(['email' => 'company@example.com']);
        User::factory()->company()->create([
            'company_id' => $company->id,
            'notify_inquiries' => true,
        ]);
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        $this->post(route('inquiries.store', $listing->slug), $this->payload())
            ->assertRedirect(route('listings.show', $listing->slug));

        Notification::assertSentOnDemandTimes(InquiryReceived::class, 1);
    }

    public function test_professional_opt_out_skips_inquiry_notification(): void
    {
        Notification::fake();

        $professionalUser = User::factory()->professional()->create(['notify_inquiries' => false]);
        $professional = Professional::factory()->create(['user_id' => $professionalUser->id]);
        $inquiry = Inquiry::factory()->create(['professional_id' => $professional->id]);

        app(InquiryNotifier::class)->notify($inquiry);

        Notification::assertNothingSent();
    }

    public function test_notification_content_has_no_submitter_phone_or_email_and_limits_the_message(): void
    {
        Notification::fake();

        $phone = '+2348099999999';
        $email = 'jane.submitter@example.com';
        $message = str_repeat('Please arrange an inspection. ', 12).'TAIL_MARKER_SHOULD_NOT_APPEAR';

        $company = Company::factory()->create(['email' => 'company@example.com']);
        $listing = Listing::factory()->published()->create([
            'company_id' => $company->id,
            'title' => 'Grand Villa in Gwarinpa',
        ]);

        $this->post(route('inquiries.store', $listing->slug), $this->payload([
            'phone' => $phone,
            'email' => $email,
            'message' => $message,
        ]))->assertRedirect(route('listings.show', $listing->slug));

        $inquiry = Inquiry::firstOrFail();

        Notification::assertSentOnDemand(
            InquiryReceived::class,
            function (InquiryReceived $notification, array $channels, $notifiable) use ($inquiry, $phone, $email, $message): bool {
                $mail = $notification->toMail($notifiable);
                $html = $mail->render();

                $this->assertStringContainsString('Grand Villa in Gwarinpa', $html);
                $this->assertStringContainsString('Consultation', $html);
                $this->assertStringContainsString('Jane', $html);
                $this->assertStringContainsString(Str::limit($message, 200), $html);
                $this->assertStringNotContainsString('TAIL_MARKER_SHOULD_NOT_APPEAR', $html);
                $this->assertStringNotContainsString($phone, $html);
                $this->assertStringNotContainsString($email, $html);
                $this->assertStringContainsString(route('inquiries.show', $inquiry), $mail->actionUrl);

                return true;
            },
        );
    }

    public function test_inquiry_still_saves_when_recipient_has_no_email(): void
    {
        Notification::fake();
        Log::spy();

        $company = Company::factory()->create(['email' => null]);
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        $this->post(route('inquiries.store', $listing->slug), $this->payload())
            ->assertRedirect(route('listings.show', $listing->slug))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('inquiries', [
            'listing_id' => $listing->id,
        ]);

        Notification::assertNothingSent();
        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message): bool => str_contains($message, 'no recipient email'))
            ->atLeast()->once();
    }

    public function test_mail_failure_never_breaks_the_submission(): void
    {
        $this->mock(InquiryNotifier::class, function ($mock): void {
            $mock->shouldReceive('notify')->andThrow(new RuntimeException('mail transport down'));
        });

        $company = Company::factory()->create(['email' => 'company@example.com']);
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        $this->post(route('inquiries.store', $listing->slug), $this->payload())
            ->assertRedirect(route('listings.show', $listing->slug))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('inquiries', [
            'listing_id' => $listing->id,
        ]);
    }

    public function test_honeypot_submission_stores_and_sends_nothing(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        $this->post(route('inquiries.store', $listing->slug), [
            'website' => 'https://spam.example',
        ])
            ->assertRedirect(route('listings.show', $listing->slug))
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('inquiries', 0);
        Notification::assertNothingSent();
    }

    public function test_sixth_inquiry_from_the_same_ip_is_throttled(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $listing = Listing::factory()->published()->create(['company_id' => $company->id]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('inquiries.store', $listing->slug), $this->payload([
                'name' => "Jane {$attempt}",
            ]))->assertRedirect(route('listings.show', $listing->slug));
        }

        $this->post(route('inquiries.store', $listing->slug), $this->payload([
            'name' => 'Jane Sixth',
        ]))->assertStatus(429);

        $this->assertDatabaseCount('inquiries', 5);
    }

    /**
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'consultation',
            'name' => 'Jane Submitter',
            'phone' => '+2348099999999',
            'email' => 'jane.submitter@example.com',
            'message' => 'I would like to inspect the property next week.',
        ], $overrides);
    }
}
