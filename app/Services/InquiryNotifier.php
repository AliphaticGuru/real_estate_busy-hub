<?php

namespace App\Services;

use App\Models\Inquiry;
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
