<?php

namespace App\Filament\Resources\DiseaseAliases;

use App\Filament\Resources\DiseaseAliases\Pages\ManageDiseaseAliases;
use App\Models\Disease;
use App\Models\DiseaseAlias;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class DiseaseAliasResource extends Resource
{
    protected static ?string $model = DiseaseAlias::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Hospital';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Disease Aliases';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('disease_id')
                ->label('Disease')
                ->options(fn () => Disease::ordered()->get()->pluck('name', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('alias.ar')->label('Alias (Arabic)')->required()->maxLength(255),
            TextInput::make('alias.en')->label('Alias (English)')->required()->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('alias')->label('Alias')->searchable(),
                TextColumn::make('disease.name')->label('Disease'),
            ])
            ->filters([
                SelectFilter::make('disease_id')
                    ->label('Disease')
                    ->options(fn () => Disease::ordered()->get()->pluck('name', 'id')),
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
        return ['index' => ManageDiseaseAliases::route('/')];
    }
}
