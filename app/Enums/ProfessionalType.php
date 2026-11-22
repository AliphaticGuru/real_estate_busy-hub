<?php

namespace App\Enums;

enum ProfessionalType: string
{
    case Lawyer = 'lawyer';
    case Architect = 'architect';
    case Engineer = 'engineer';
    case QuantitySurveyor = 'quantity_surveyor';
    case Builder = 'builder';

    /**
     * Human readable label for the professional type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Lawyer => 'Lawyer',
            self::Architect => 'Architect',
            self::Engineer => 'Engineer',
            self::QuantitySurveyor => 'Quantity Surveyor',
            self::Builder => 'Builder',
        };
    }
}
