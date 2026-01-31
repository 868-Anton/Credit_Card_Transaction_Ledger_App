<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Posted = 'posted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Posted => 'Posted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Posted => 'success',
        };
    }

    public static function default(): self
    {
        return self::Pending;
    }
}
