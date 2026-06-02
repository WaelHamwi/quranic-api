<?php

namespace App\Filament\Resources\AdhkarSections;

use App\Filament\Resources\AdhkarSections\Pages\ManageAdhkarSections;
use App\Models\AdhkarCategory;
use App\Models\AdhkarSection;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AdhkarSectionResource extends Resource
{
    protected static ?string $model = AdhkarSection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static string|UnitEnum|null $navigationGroup = 'Adhkar';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('adhkar_category_id')
                ->label('Adhkar Category')
                ->options(fn () => AdhkarCategory::ordered()->get()->pluck('name', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->maxLength(255),
            TextInput::make('name.en')->label('Name (English)')->required()->maxLength(255),
            Toggle::make('order_randomly')
                ->label('Order Randomly')
                ->helperText('When on, this section\'s items are shuffled into a random order every time it is viewed. When off, items follow their manual sequence number.'),
            TextInput::make('display_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('category.name')->label('Category'),
                IconColumn::make('order_randomly')->label('Random')->boolean(),
                TextColumn::make('display_order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->filters([
                SelectFilter::make('adhkar_category_id')
                    ->label('Category')
                    ->options(fn () => AdhkarCategory::ordered()->get()->pluck('name', 'id')),
            ])
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
        return ['index' => ManageAdhkarSections::route('/')];
    }
}
