<?php

namespace App\Enums;

enum ProfessionalType: string
{
    case Lawyer = 'lawyer';
    case Architect = 'architect';
    case Engineer = 'engineer';
    case QuantitySurveyor = 'quantity_surveyor';
    case Builder = 'builder';
}
