<?php

namespace App\Enums;

enum MaterialAidStatus: string
{
    case Yes = 'yes';
    case No = 'no';
    case NotApplicable = 'not_applicable';

    public function label(): string
    {
        return match ($this) {
            self::Yes => 'Так',
            self::No => 'Ні',
            self::NotApplicable => 'Не передбачено',
        };
    }
}
