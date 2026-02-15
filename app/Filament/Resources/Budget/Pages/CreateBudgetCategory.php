<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetCategory extends CreateRecord
{
    protected static string $resource = BudgetCategoryResource::class;
}
