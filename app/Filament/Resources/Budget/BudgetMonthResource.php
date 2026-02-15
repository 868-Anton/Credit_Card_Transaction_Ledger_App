<?php

namespace App\Filament\Resources\Budget;

use App\Filament\Resources\Budget\Pages\ListBudgetMonths;
use App\Filament\Resources\Budget\Pages\ViewBudgetMonth;
use App\Filament\Resources\Budget\RelationManagers\LineItemsRelationManager;
use App\Filament\Resources\Budget\RelationManagers\LiveIncomeRelationManager;
use App\Filament\Resources\Budget\RelationManagers\ProjectedIncomeRelationManager;
use App\Filament\Resources\Budget\Schemas\BudgetMonthForm;
use App\Filament\Resources\Budget\Tables\BudgetMonthTable;
use App\Models\BudgetMonth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BudgetMonthResource extends Resource
{
    protected static ?string $model = BudgetMonth::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|\UnitEnum|null $navigationGroup = 'Budget';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Monthly Budgets';

    public static function form(Schema $schema): Schema
    {
        return BudgetMonthForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return BudgetMonthTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LineItemsRelationManager::class,
            ProjectedIncomeRelationManager::class,
            LiveIncomeRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBudgetMonths::route('/'),
            'view' => ViewBudgetMonth::route('/{record}'),
        ];
    }
}
