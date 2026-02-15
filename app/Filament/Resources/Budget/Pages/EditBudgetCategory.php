<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditBudgetCategory extends EditRecord
{
    protected static string $resource = BudgetCategoryResource::class;
}
