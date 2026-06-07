<?php

namespace App\Filament\Resources\Diseases\Schemas;

use App\Models\Subcategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class DiseaseForm
{
    public static function getSchema(): array
    {
        return [
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
        ];
    }
}
