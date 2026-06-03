<?php

namespace App\Filament\Resources\TahsinatItems;

use App\Filament\Resources\TahsinatItems\Pages\ManageTahsinatItems;
use App\Models\TahsinatCategory;
use App\Models\TahsinatItem;
use App\Models\TahsinatSection;
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

class TahsinatItemResource extends Resource
{
    protected static ?string $model = TahsinatItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Tahsinat';

    protected static ?int $navigationSort = 3;

    public const APPLICABILITY = [
        'self'   => 'Self (تحصين النفس)',
        'others' => 'Others (تحصين الغير)',
        'both'   => 'Both',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tahsinat_category_id')
                ->label('Category')
                ->options(fn () => TahsinatCategory::ordered()->get()->pluck('name', 'id'))
                ->helperText('Self-Fortification or Fortification for Others.')
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('tahsinat_section_id', null)),
            Select::make('tahsinat_section_id')
                ->label('Section (optional)')
                ->options(fn (Get $get) => $get('tahsinat_category_id')
                    ? TahsinatSection::where('tahsinat_category_id', $get('tahsinat_category_id'))
                        ->ordered()->get()->pluck('name', 'id')
                    : [])
                ->helperText('Group this item under a sub-section of the category.')
                ->searchable(),
            Select::make('applicability')
                ->label('Applicability')
                ->options(self::APPLICABILITY)
                ->default('both')
                ->required(),
            TextInput::make('label.ar')->label('Label (Arabic)')->maxLength(255),
            TextInput::make('label.en')->label('Label (English)')->maxLength(255),
            Textarea::make('text.ar')->label('Text (Arabic)')->rows(3)
                ->helperText('Provide text and/or an image.'),
            Textarea::make('text.en')->label('Text (English)')->rows(3),
            FileUpload::make('image')
                ->label('Image (optional)')
                ->image()
                ->disk('public')
                ->directory('tahsinat'),
            TextInput::make('repetitions')->numeric()->minValue(1)->default(1)->required()
                ->helperText('How many times this should be repeated.'),
            Textarea::make('hint.ar')->label('Hint (Arabic)')->rows(2)
                ->helperText('Helpful explanation shown to the user.'),
            Textarea::make('hint.en')->label('Hint (English)')->rows(2),
            TextInput::make('display_order')->numeric()->default(0)
                ->helperText('Manual sequence number within the section (1, 2, 3...).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Label')->searchable(),
                ImageColumn::make('image')->label('Image')->disk('public'),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('section.name')->label('Section'),
                TextColumn::make('applicability')->label('For')->badge(),
                TextColumn::make('repetitions')->label('Reps'),
                TextColumn::make('display_order')->label('Order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->filters([
                SelectFilter::make('tahsinat_category_id')
                    ->label('Category')
                    ->options(fn () => TahsinatCategory::ordered()->get()->pluck('name', 'id')),
                SelectFilter::make('tahsinat_section_id')
                    ->label('Section')
                    ->options(fn () => TahsinatSection::ordered()->get()->pluck('name', 'id')),
                SelectFilter::make('applicability')->options(self::APPLICABILITY),
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
