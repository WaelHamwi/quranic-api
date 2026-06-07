<?php

namespace App\Filament\Resources\Recordings\Schemas;

use App\Models\Category;
use App\Models\Disease;
use App\Models\Subcategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;

class RecordingForm
{
    public static function getSchema(): array
    {
        return [
            // A recording attaches to exactly ONE level (the leaf): a disease,
            // a subcategory (with no diseases), or a category (with no
            // subcategories). Picking one disables the other two.
            Select::make('disease_id')
                ->label('Disease')
                ->options(fn () => Disease::ordered()->get()->pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->live()
                ->disabled(fn (Get $get) => filled($get('category_id')) || filled($get('subcategory_id')))
                ->helperText('Attach to a Disease — OR pick a Subcategory/Category below instead.'),
            Select::make('subcategory_id')
                ->label('Subcategory (without diseases)')
                ->options(fn () => Subcategory::doesntHave('diseases')->ordered()->get()->pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->live()
                ->disabled(fn (Get $get) => filled($get('disease_id')) || filled($get('category_id')))
                ->helperText('Only subcategories that have no diseases can hold recordings directly.'),
            Select::make('category_id')
                ->label('Category (without subcategories)')
                ->options(fn () => Category::doesntHave('subcategories')->ordered()->get()->pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->live()
                ->disabled(fn (Get $get) => filled($get('disease_id')) || filled($get('subcategory_id')))
                ->helperText('Only categories that have no subcategories can hold recordings directly.'),
            TextInput::make('title.ar')->label('Title (Arabic)')->maxLength(255),
            TextInput::make('title.en')->label('Title (English)')->maxLength(255),
            Textarea::make('description.ar')->label('Description (Arabic)')->rows(4),
            Textarea::make('description.en')->label('Description (English)')->rows(4),
            FileUpload::make('audio_path')
                ->label('Recording File')
                ->disk('public')
                ->directory('recordings')
                ->acceptedFileTypes([
                    'audio/mpeg', 'audio/mp3', 'audio/x-mpeg', 'audio/x-mp3',
                    'audio/mp4', 'audio/ogg', 'audio/wav', 'audio/x-wav',
                    'audio/webm', 'video/mp4',
                ])
                ->maxSize(204800)
                ->helperText('Accepted: mp3, mp4, ogg, wav (max 200 MB).'),
            TextInput::make('duration_seconds')->numeric()->minValue(0),
            Toggle::make('is_free')
                ->label('Free Session')
                ->helperText('Mark this as the free session for its disease / category. Enabling it will automatically lock whichever session was free before.'),
            Toggle::make('is_general')
                ->label('General Ruqyah')
                ->helperText('Include this recording in the General Ruqyah playlist.'),
        ];
    }
}
