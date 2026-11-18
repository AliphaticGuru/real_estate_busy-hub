<?php

namespace App\Enums;

enum ListingVerificationStatus: string
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
