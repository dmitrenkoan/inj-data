<?php

namespace App\Enums;

enum PaymentIssueType: string
{
    case MonetaryAllowance = 'monetary_allowance';
    case AdditionalRemuneration = 'additional_remuneration';

    public function label(): string
    {
        return match ($this) {
            self::MonetaryAllowance => 'Грошове забезпечення',
            self::AdditionalRemuneration => 'Додаткова винагорода',
        };
    }
}
