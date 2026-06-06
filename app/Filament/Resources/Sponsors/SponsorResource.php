<?php

namespace App\Filament\Resources\Sponsors;

use App\Filament\Resources\Sponsors\Pages\ManageSponsors;
use App\Models\Sponsor;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SponsorResource extends Resource
{
    protected static ?string $model = Sponsor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->maxLength(255),
            TextInput::make('name.en')->label('Name (English)')->required()->maxLength(255),
            FileUpload::make('logo_path')
                ->label('Logo')
                ->image()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(5120)
                ->disk('public')
                ->directory('sponsors')
                ->helperText('PNG, JPG, WEBP, GIF or SVG (max 5 MB).'),
            TextInput::make('website_url')->url()->maxLength(255),
            Toggle::make('target_all_countries')
                ->label('Show to all countries')
                ->helperText('On = the sponsor screen shows to every user regardless of country.')
                ->default(true)
                ->live(),
            Select::make('target_countries')
                ->label('Target countries')
                ->multiple()
                ->searchable()
                ->native(false)
                // Show every country (default cap is 50) so the user can scroll
                // the whole list / keep loading more as they scroll.
                ->optionsLimit(fn () => \count((array) config('countries')))
                ->options(fn () => array_combine(config('countries'), config('countries')))
                ->helperText('Only users from these countries will see this sponsor.')
                ->visible(fn (Get $get) => ! $get('target_all_countries'))
                ->required(fn (Get $get) => ! $get('target_all_countries')),
            Select::make('target_genders')
                ->multiple()
                ->options(['male' => 'Male', 'female' => 'Female'])
                ->helperText('Leave empty for all.'),
            Toggle::make('is_featured'),
            Toggle::make('display_on_launch')->label('Show on splash screen'),
            TextInput::make('display_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')->label('Logo')->disk('public'),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('website_url')->label('Website')->limit(30),
                IconColumn::make('target_all_countries')->label('All countries')->boolean(),
                TextColumn::make('target_countries')
                    ->label('Countries')
                    ->badge()
                    ->placeholder('All')
                    ->limitList(2),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                IconColumn::make('display_on_launch')->label('On Launch')->boolean(),
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
        return ['index' => ManageSponsors::route('/')];
    }
}
