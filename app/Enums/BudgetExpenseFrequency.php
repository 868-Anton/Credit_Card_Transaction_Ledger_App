<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BudgetExpenseFrequency: string implements HasColor, HasLabel
{
    case Recurring = 'recurring';
    case OneOff = 'one_off';

    public function getLabel(): string
    {
        return match ($this) {
            self::Recurring => 'Recurring',
            self::OneOff => 'One-off',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Recurring => 'success',
            self::OneOff => 'warning',
        };
    }
}
