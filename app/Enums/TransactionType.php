<?php

namespace App\Enums;

enum TransactionType: string
{
    case Charge = 'charge';
    case Payment = 'payment';
    case Refund = 'refund';
    case Fee = 'fee';

    public function label(): string
    {
        return match ($this) {
            self::Charge => 'Charge',
            self::Payment => 'Payment',
            self::Refund => 'Refund',
            self::Fee => 'Fee',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Charge => 'danger',
            self::Payment => 'success',
            self::Refund => 'info',
            self::Fee => 'warning',
        };
    }

    /**
     * Returns the expected sign direction for this type.
     * Used by the model to validate or auto-correct amounts.
     *
     * @return int 1 = must be positive, -1 = must be negative
     */
    public function expectedSign(): int
    {
        return match ($this) {
            self::Charge => 1,
            self::Fee => 1,
            self::Payment => -1,
            self::Refund => -1,
        };
    }

    public static function default(): self
    {
        return self::Charge;
    }
}
