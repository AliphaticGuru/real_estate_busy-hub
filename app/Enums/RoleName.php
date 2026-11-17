<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case Company = 'company';
    case Professional = 'professional';
    case Verifier = 'verifier';
    case Customer = 'customer';

    /**
     * Human readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Company => 'Company',
            self::Professional => 'Professional',
            self::Verifier => 'Verifier',
            self::Customer => 'Customer',
        };
    }
}
