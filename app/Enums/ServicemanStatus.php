<?php

namespace App\Enums;

enum ServicemanStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Unreachable = 'unreachable';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'В роботі',
            self::Completed => 'Завершено',
            self::Unreachable => 'Не вдалось зв\'язатись',
        };
    }
}
