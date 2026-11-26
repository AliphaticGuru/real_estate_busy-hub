<?php

namespace App\Services;

use App\Models\User;

class AvatarPalette
{
    /**
     * Resolve the deterministic palette colour for a user.
     */
    public static function colorFor(User $user): string
    {
        $palette = config('avatar.palette');

        $index = hexdec(hash('crc32b', (string) $user->id)) % count($palette);

        return $palette[$index];
    }
}
