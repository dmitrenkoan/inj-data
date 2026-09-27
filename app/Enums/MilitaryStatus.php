<?php

namespace App\Enums;

enum MilitaryStatus: string
{
    case Active = 'active';
    case Discharged = 'discharged';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Діючий',
            self::Discharged => 'Звільнений',
        };
    }
}
