<?php

namespace App\Filament\Resources\Recordings\Pages;

use App\Filament\Resources\Recordings\RecordingResource;
use App\Jobs\CompressAudioJob;
use App\Models\Recording;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRecordings extends ManageRecords
{
    protected static string $resource = RecordingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->after(function (Recording $record): void {
                    if ($record->audio_path && ! str_starts_with($record->audio_path, 'http')) {
                        CompressAudioJob::dispatch(Recording::class, $record->id, $record->audio_path);
                    }
                }),
        ];
    }
}
