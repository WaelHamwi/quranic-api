<?php

namespace App\Filament\Resources\Diseases;

use App\Filament\Resources\Diseases\Pages\ManageDiseases;
use App\Models\Disease;
use App\Models\Subcategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class DiseaseResource extends Resource
{
    protected static ?string $model = Disease::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bug-ant';

    protected static string|UnitEnum|null $navigationGroup = 'Hospital';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subcategory_id')
                ->label('Subcategory')
                ->options(fn () => Subcategory::doesntHave('recordings')->ordered()->get()->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->helperText('Only subcategories without directly-attached recordings can have diseases.'),
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->maxLength(255),
            TextInput::make('name.en')
                ->label('Name (English)')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
            FileUpload::make('icon')
                ->label('Icon')
                ->image()
                ->acceptedFileTypes(['image/png', 'image/svg+xml'])
                ->maxSize(500)
                ->disk('public')
                ->directory('diseases')
                ->helperText('PNG or SVG, max 500 KB.'),
            TextInput::make('display_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('icon')->label('Icon')->disk('public'),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('subcategory.name')->label('Subcategory'),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('recordings_count')->counts('recordings')->label('Recordings'),
                TextColumn::make('display_order')->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('display_order')
            ->filters([
                SelectFilter::make('subcategory_id')
                    ->label('Subcategory')
                    ->options(fn () => Subcategory::ordered()->get()->pluck('name', 'id')),
                SelectFilter::make('is_active')->options(['1' => 'Active', '0' => 'Inactive']),
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
        return ['index' => ManageDiseases::route('/')];
    }
}
