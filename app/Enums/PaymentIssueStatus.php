<?php

namespace App\Enums;

enum PaymentIssueStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'В роботі',
            self::Completed => 'Завершено',
        };
    }
}
