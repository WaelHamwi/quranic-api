<?php

namespace App\Filament\Resources\AdhkarItems;

use App\Filament\Resources\AdhkarItems\Pages\ManageAdhkarItems;
use App\Models\AdhkarCategory;
use App\Models\AdhkarItem;
use App\Models\AdhkarSection;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AdhkarItemResource extends Resource
{
    protected static ?string $model = AdhkarItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|UnitEnum|null $navigationGroup = 'Adhkar';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('adhkar_category_id')
                ->label('Category')
                ->options(fn () => AdhkarCategory::ordered()->get()->pluck('name', 'id'))
                ->helperText('Morning, Evening, Sleep or Waking.')
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('adhkar_section_id', null)),
            Select::make('adhkar_section_id')
                ->label('Section (optional)')
                ->options(fn (Get $get) => $get('adhkar_category_id')
                    ? AdhkarSection::where('adhkar_category_id', $get('adhkar_category_id'))
                        ->ordered()->get()->pluck('name', 'id')
                    : [])
                ->helperText('Group this item under a sub-section of the category.')
                ->searchable(),
            Textarea::make('text.ar')->label('Text (Arabic)')->rows(3)
                ->helperText('Provide text and/or an image.'),
            Textarea::make('text.en')->label('Text (English)')->rows(3),
            FileUpload::make('image')
                ->label('Image (optional)')
                ->image()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(5120)
                ->disk('public')
                ->directory('adhkar'),
            TextInput::make('repetitions')->numeric()->minValue(1)->default(1)->required()
                ->helperText('How many times this dhikr should be repeated.'),
            Textarea::make('hint.ar')->label('Hint (Arabic)')->rows(2)
                ->helperText('Helpful explanation shown to the user.'),
            Textarea::make('hint.en')->label('Hint (English)')->rows(2),
            Textarea::make('daleel.ar')->label('Daleel / Source (Arabic)')->rows(2),
            Textarea::make('daleel.en')->label('Daleel / Source (English)')->rows(2),
            TextInput::make('display_order')->numeric()->default(0)
                ->helperText('Manual sequence number within the section (1, 2, 3...).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('text')->label('Text')->limit(50)->searchable(),
                ImageColumn::make('image')->label('Image')->disk('public'),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('section.name')->label('Section'),
                TextColumn::make('repetitions')->label('Reps'),
                TextColumn::make('display_order')->label('Order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->filters([
                SelectFilter::make('adhkar_category_id')
                    ->label('Category')
                    ->options(fn () => AdhkarCategory::ordered()->get()->pluck('name', 'id')),
                SelectFilter::make('adhkar_section_id')
                    ->label('Section')
                    ->options(fn () => AdhkarSection::ordered()->get()->pluck('name', 'id')),
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
        return ['index' => ManageAdhkarItems::route('/')];
    }
}
