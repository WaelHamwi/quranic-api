<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('slug', Str::slug($state));
                }),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique('categories', 'slug', ignoreRecord: true),
            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->default(true)
                ->label('Active'),
        ];
    }
}