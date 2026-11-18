<?php

namespace App\Enums;

enum ListingPropertyType: string
{
    case House = 'house';
    case Apartment = 'apartment';
    case Duplex = 'duplex';
    case Villa = 'villa';
    case Townhouse = 'townhouse';
    case Land = 'land';
    case Office = 'office';
}
