<?php

namespace App\Filament\Resources\Recordings;

use App\Filament\Resources\Recordings\Pages\ManageRecordings;
use App\Jobs\CompressAudioJob;
use App\Models\Category;
use App\Models\Disease;
use App\Models\Recording;
use App\Models\Subcategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class RecordingResource extends Resource
{
    protected static ?string $model = Recording::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-musical-note';

    protected static string|UnitEnum|null $navigationGroup = 'Hospital';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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
            TextInput::make('title.ar')->label('Title (Arabic)')->required()->maxLength(255),
            TextInput::make('title.en')->label('Title (English)')->required()->maxLength(255),
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
            Toggle::make('is_general')
                ->label('General Ruqyah')
                ->helperText('Include this recording in the General Ruqyah playlist.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Title')->searchable(),
                TextColumn::make('disease.name')->label('Disease')->placeholder('—'),
                TextColumn::make('subcategory.name')->label('Subcategory')->placeholder('—'),
                TextColumn::make('category.name')->label('Category')->placeholder('—'),
                TextColumn::make('session_number')->label('Session')->sortable(),
                ToggleColumn::make('is_general')->label('General Ruqyah'),
                TextColumn::make('duration_seconds')->label('Duration (s)'),
                TextColumn::make('plays_count')->label('Plays')->sortable(),
            ])
            ->defaultSort('session_number')
            ->filters([
                SelectFilter::make('disease_id')
                    ->label('Disease')
                    ->options(fn () => Disease::ordered()->get()->pluck('name', 'id')),
                SelectFilter::make('subcategory_id')
                    ->label('Subcategory')
                    ->options(fn () => Subcategory::doesntHave('diseases')->ordered()->get()->pluck('name', 'id')),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::doesntHave('subcategories')->ordered()->get()->pluck('name', 'id')),
                SelectFilter::make('is_general')->options(['1' => 'General Ruqyah', '0' => 'Disease-specific']),
            ])
            ->recordActions([
                Action::make('listen')
                    ->label('Listen')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->hidden(fn ($record) => ! $record->audio_path)
                    ->modalContent(function ($record) {
                        return view('filament.recordings.audio-player-modal', [
                            'audioUrl'      => $record->streamUrl(),
                            'title'         => $record->title,
                            'sessionNumber' => $record->session_number,
                        ]);
                    })
                    ->modalHeading(fn ($record) => $record->title)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                EditAction::make()
                    ->after(function (Recording $record): void {
                        if ($record->audio_path && ! str_starts_with($record->audio_path, 'http')) {
                            CompressAudioJob::dispatch(Recording::class, $record->id, $record->audio_path);
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRecordings::route('/')];
    }
}
