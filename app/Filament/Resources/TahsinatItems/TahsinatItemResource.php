<?php

namespace App\Filament\Resources\TahsinatItems;

use App\Filament\Resources\TahsinatItems\Pages\ManageTahsinatItems;
use App\Models\TahsinatCategory;
use App\Models\TahsinatItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TahsinatItemResource extends Resource
{
    protected static ?string $model = TahsinatItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Tahsinat';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tahsinat_category_id')
                ->label('Tahsinat Category')
                ->options(fn () => TahsinatCategory::ordered()->get()->pluck('name', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('label.ar')->label('Label (Arabic)')->required()->maxLength(255),
            TextInput::make('label.en')->label('Label (English)')->required()->maxLength(255),
            Textarea::make('text.ar')->label('Text (Arabic)')->required()->rows(3),
            Textarea::make('text.en')->label('Text (English)')->required()->rows(3),
            TextInput::make('repetitions')->numeric()->minValue(1)->default(1)->required(),
            Textarea::make('hint.ar')->label('Hint (Arabic)')->rows(2),
            Textarea::make('hint.en')->label('Hint (English)')->rows(2),
            TextInput::make('display_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Label')->searchable(),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('repetitions')->label('Reps'),
                TextColumn::make('display_order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->filters([
                SelectFilter::make('tahsinat_category_id')
                    ->label('Category')
                    ->options(fn () => TahsinatCategory::ordered()->get()->pluck('name', 'id')),
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
        return ['index' => ManageTahsinatItems::route('/')];
    }
}
