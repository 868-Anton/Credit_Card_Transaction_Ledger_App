<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RentalInvoiceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case Overdue = 'overdue';

    public function getLabel(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::Unpaid => 'Unpaid',
            self::Overdue => 'Overdue',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Unpaid => 'gray',
            self::Overdue => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Paid => 'heroicon-o-check-circle',
            self::Unpaid => 'heroicon-o-clock',
            self::Overdue => 'heroicon-o-exclamation-triangle',
        };
    }
}
