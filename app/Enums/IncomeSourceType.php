<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IncomeSourceType: string implements HasColor, HasLabel
{
    case Salary = 'salary';
    case Rental = 'rental';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Salary => 'Salary',
            self::Rental => 'Rental',
            self::Other => 'Other',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Salary => 'success',
            self::Rental => 'info',
            self::Other => 'gray',
        };
    }
}
