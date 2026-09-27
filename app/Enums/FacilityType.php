<?php

namespace App\Enums;

enum FacilityType: string
{
    case Moz = 'moz';
    case Mou = 'mou';
    case Foreign = 'foreign';

    public function label(): string
    {
        return match ($this) {
            self::Moz => 'МОЗ',
            self::Mou => 'МОУ',
            self::Foreign => 'Іноземний',
        };
    }
}
