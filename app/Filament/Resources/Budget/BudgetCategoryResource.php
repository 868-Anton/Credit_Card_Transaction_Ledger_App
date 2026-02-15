<?php

namespace App\Filament\Resources\Budget;

use App\Filament\Resources\Budget\Pages\CreateBudgetCategory;
use App\Filament\Resources\Budget\Pages\EditBudgetCategory;
use App\Filament\Resources\Budget\Pages\ListBudgetCategories;
use App\Filament\Resources\Budget\Schemas\BudgetCategoryForm;
use App\Filament\Resources\Budget\Tables\BudgetCategoryTable;
use App\Models\BudgetCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BudgetCategoryResource extends Resource
{
    protected static ?string $model = BudgetCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Budget';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Categories';

    public static function form(Schema $schema): Schema
    {
        return BudgetCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetCategoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBudgetCategories::route('/'),
            'create' => CreateBudgetCategory::route('/create'),
            'edit' => EditBudgetCategory::route('/{record}/edit'),
        ];
    }
}
