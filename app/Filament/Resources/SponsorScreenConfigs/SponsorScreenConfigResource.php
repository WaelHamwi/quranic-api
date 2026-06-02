<?php

namespace App\Filament\Resources\SponsorScreenConfigs;

use App\Filament\Resources\SponsorScreenConfigs\Pages\ManageSponsorScreenConfig;
use App\Models\Sponsor;
use App\Models\SponsorScreenConfig;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SponsorScreenConfigResource extends Resource
{
    protected static ?string $model = SponsorScreenConfig::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Sponsor Screen';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('is_enabled')->label('Show sponsor splash screen')->default(true),
            TextInput::make('display_duration_seconds')
                ->numeric()
                ->minValue(1)
                ->default(3)
                ->required(),
            Select::make('selected_sponsor_id')
                ->label('Sponsor to display')
                ->options(fn () => Sponsor::ordered()->get()->pluck('name', 'id'))
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')->label('Enabled')->boolean(),
                TextColumn::make('display_duration_seconds')->label('Duration (s)'),
                TextColumn::make('sponsor.name')->label('Sponsor')->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSponsorScreenConfig::route('/')];
    }
}
