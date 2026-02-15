<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetExpenseTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetExpenseTemplate extends CreateRecord
{
    protected static string $resource = BudgetExpenseTemplateResource::class;
}
