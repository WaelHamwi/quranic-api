<?php

namespace App\Filament\Resources\AdhkarCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class AdhkarCategoryForm
{
    public static function getSchema(): array
    {
        return [
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->maxLength(255),
            TextInput::make('name.en')->label('Name (English)')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
            FileUpload::make('icon')
                ->label('Icon')
                ->image()
                ->acceptedFileTypes(['image/png', 'image/svg+xml'])
                ->maxSize(500)
                ->disk('public')
                ->directory('adhkar-categories')
                ->helperText('PNG or SVG, max 500 KB. Shown on the category card in the app.'),
            TextInput::make('day_number')->numeric()->minValue(1)->maxValue(7)
                ->helperText('Optional day in the 7-day rotation.'),
            TextInput::make('display_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ];
    }
}
