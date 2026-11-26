<?php

namespace App\Services;

class WhatsApp
{
    /**
     * Build a wa.me deep link for a message, using digits-only numbers.
     */
    public static function link(string $phone, string $message): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
