<?php

namespace App\Filament\Resources\AdhkarCategories;

use App\Filament\Resources\AdhkarCategories\Pages\ManageAdhkarCategories;
use App\Models\AdhkarCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AdhkarCategoryResource extends Resource
{
    protected static ?string $model = AdhkarCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sun';

    protected static string|UnitEnum|null $navigationGroup = 'Adhkar';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->maxLength(255),
            TextInput::make('name.en')->label('Name (English)')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('day_number')->numeric()->minValue(1)->maxValue(7)
                ->helperText('Optional day in the 7-day rotation.'),
            TextInput::make('display_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('day_number')->label('Day'),
                TextColumn::make('items_count')->counts('items')->label('Items'),
                TextColumn::make('display_order')->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('display_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAdhkarCategories::route('/')];
    }
}
