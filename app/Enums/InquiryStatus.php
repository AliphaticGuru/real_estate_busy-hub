<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Closed = 'closed';

    public function badgeVariant(): string
    {
        return match ($this->value) {
            'verified', 'published', 'closed' => 'success',
            'in_review', 'contacted' => 'warning',
            'rejected' => 'danger',
            default => 'neutral',
        };
    }
}
