<?php

namespace App\Filament\Resources\Budget;

use App\Filament\Resources\Budget\Pages\CreateBudgetExpenseTemplate;
use App\Filament\Resources\Budget\Pages\EditBudgetExpenseTemplate;
use App\Filament\Resources\Budget\Pages\ListBudgetExpenseTemplates;
use App\Filament\Resources\Budget\Schemas\BudgetExpenseTemplateForm;
use App\Filament\Resources\Budget\Tables\BudgetExpenseTemplateTable;
use App\Models\BudgetExpenseTemplate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BudgetExpenseTemplateResource extends Resource
{
    protected static ?string $model = BudgetExpenseTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|\UnitEnum|null $navigationGroup = 'Budget';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Expense Templates';

    public static function form(Schema $schema): Schema
    {
        return BudgetExpenseTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetExpenseTemplateTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBudgetExpenseTemplates::route('/'),
            'create' => CreateBudgetExpenseTemplate::route('/create'),
            'edit' => EditBudgetExpenseTemplate::route('/{record}/edit'),
        ];
    }
}
