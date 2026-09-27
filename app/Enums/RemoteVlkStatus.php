<?php

namespace App\Enums;

enum RemoteVlkStatus: string
{
    case NeedsProvision = 'needs_provision';
    case Provided = 'provided';

    public function label(): string
    {
        return match ($this) {
            self::NeedsProvision => 'Потребує забезпечення',
            self::Provided => 'Забезпечено',
        };
    }
}
