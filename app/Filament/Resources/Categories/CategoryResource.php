<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;
class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;


    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';


    protected static string|UnitEnum|null $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components(CategoryForm::getSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(CategoriesTable::getColumns())
            ->filters(CategoriesTable::getFilters())
            ->actions(CategoriesTable::getActions())
            ->bulkActions(CategoriesTable::getBulkActions());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}