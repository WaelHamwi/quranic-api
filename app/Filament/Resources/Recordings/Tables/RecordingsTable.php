<?php

namespace App\Filament\Resources\Recordings\Tables;

use App\Jobs\CompressAudioJob;
use App\Models\Category;
use App\Models\Disease;
use App\Models\Recording;
use App\Models\Subcategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;

class RecordingsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('title')->label('Title')->searchable(),
            TextColumn::make('disease.name')->label('Disease')->placeholder('—'),
            TextColumn::make('subcategory.name')->label('Subcategory')->placeholder('—'),
            TextColumn::make('category.name')->label('Category')->placeholder('—'),
            TextColumn::make('session_number')->label('Session')->sortable(),
            ToggleColumn::make('is_free')->label('Free'),
            ToggleColumn::make('is_general')->label('General Ruqyah'),
            TextColumn::make('duration_seconds')->label('Duration (s)'),
            TextColumn::make('plays_count')->label('Plays')->sortable(),
        ];
    }

    public static function getFilters(): array
    {
        return [
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
        ];
    }

    public static function getActions(): array
    {
        return [
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
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ];
    }
}
