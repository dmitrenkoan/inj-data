<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Brigade = 'brigade';
    case Battalion = 'battalion';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Супер адмін',
            self::Brigade => 'Користувач військової частини',
            self::Battalion => 'Користувач батальйону',
        };
    }
}
