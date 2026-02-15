<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetExpenseTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditBudgetExpenseTemplate extends EditRecord
{
    protected static string $resource = BudgetExpenseTemplateResource::class;
}
