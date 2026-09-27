<?php

namespace App\Enums;

enum Severity: string
{
    case Light = 'light';
    case Severe = 'severe';

    public function label(): string
    {
        return match ($this) {
            self::Light => 'Легка',
            self::Severe => 'Тяжка',
        };
    }
}
