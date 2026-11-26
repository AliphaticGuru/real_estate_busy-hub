<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\User;
use App\Notifications\InquiryReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class InquiryNotifier
{
    /**
     * Notify the inquiry recipient without ever breaking the submission flow.
     */
    public function notify(Inquiry $inquiry): void
    {
        $recipient = $this->recipientUser($inquiry);

        if ($recipient !== null && ! $recipient->notify_inquiries) {
            Log::info('Inquiry notification skipped: recipient opted out.', [
                'inquiry_id' => $inquiry->id,
            ]);

            return;
        }

        $email = $this->recipientEmail($inquiry);

        if ($email === null) {
            Log::warning('Inquiry notification skipped: no recipient email.', [
                'inquiry_id' => $inquiry->id,
            ]);

            return;
        }

        Notification::route('mail', $email)->notify(new InquiryReceived($inquiry));
    }

    /**
     * Resolve the account that controls the inquiry email preference.
     * Professionals use their own user; company inquiries use the
     * company's owner (the earliest linked user). Legacy companies with
     * no linked user still notify their company email.
     */
    public function recipientUser(Inquiry $inquiry): ?User
    {
        if ($inquiry->professional_id !== null) {
            return $inquiry->professional?->user;
        }

        return $inquiry->listing?->company?->users()->orderBy('id')->first();
    }

    /**
     * Resolve the recipient email: the addressed professional first, then the
     * listing company.
     */
    public function recipientEmail(Inquiry $inquiry): ?string
    {
        if ($inquiry->professional_id !== null) {
            return $inquiry->professional?->user?->email;
        }

        return $inquiry->listing?->company?->email;
    }
}
