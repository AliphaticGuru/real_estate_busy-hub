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

    /**
     * The statuses this status may transition to.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Contacted, self::Closed],
            self::Contacted => [self::Closed],
            self::Closed => [self::Contacted],
        };
    }
}
