<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\User;
use App\Models\VerificationCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_status_change_writes_an_audit_log(): void
    {
        $listing = Listing::factory()->create([
            'verification_status' => 'pending',
        ]);

        $verifier = User::factory()->verifier()->create();

        $listing->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifier->id,
        ]);

        $audit = AuditLog::where('auditable_type', 'listing')
            ->where('auditable_id', $listing->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('listing.verification_status.updated', $audit->action);
        $this->assertSame($verifier->id, $audit->user_id);
        $this->assertSame('pending', $audit->old_values['verification_status']);
        $this->assertSame('verified', $audit->new_values['verification_status']);
    }

    public function test_verification_check_update_writes_an_audit_log(): void
    {
        $check = VerificationCheck::factory()->create();

        $checker = User::factory()->verifier()->create();

        $check->update([
            'status' => 'passed',
            'checked_at' => now(),
            'checked_by' => $checker->id,
        ]);

        $audit = AuditLog::where('auditable_type', 'verification_check')
            ->where('auditable_id', $check->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('verification_check.updated', $audit->action);
        $this->assertSame('pending', $audit->old_values['status']);
        $this->assertSame('passed', $audit->new_values['status']);
    }

    public function test_audit_logs_are_append_only(): void
    {
        $audit = AuditLog::factory()->create();

        $this->assertFalse($audit->save());
        $this->assertTrue($audit->delete());

        $this->assertDatabaseCount('audit_logs', 1);
    }
}
