<?php

namespace App\Enums;

enum DisabilityGroup: string
{
    case None = 'none';
    case First = 'I';
    case Second = 'II';
    case Third = 'III';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Не встановлено',
            self::First => 'I група',
            self::Second => 'II група',
            self::Third => 'III група',
        };
    }
}
