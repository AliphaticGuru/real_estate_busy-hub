<?php

namespace App\Enums;

enum CheckKey: string
{
    case TitleDocument = 'title_document';
    case SurveyPlan = 'survey_plan';
    case OwnershipProof = 'ownership_proof';
    case SiteInspection = 'site_inspection';
    case NoEncumbrance = 'no_encumbrance';

    /** All checklist keys, in workflow order. */
    public static function values(): array
    {
        return [
            self::TitleDocument->value,
            self::SurveyPlan->value,
            self::OwnershipProof->value,
            self::SiteInspection->value,
            self::NoEncumbrance->value,
        ];
    }
}
