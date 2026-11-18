<?php

namespace App\Enums;

enum CheckStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Failed = 'failed';
}
