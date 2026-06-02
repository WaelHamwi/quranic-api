<?php

namespace App\Filament\Widgets;

use App\Models\Recitation;
use App\Models\Reciter;
use App\Models\Surah;
use App\Models\Verse;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuranStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalSurahs      = Surah::count();
        $makkiCount       = Surah::where('type', 'Meccan')->count();
        $madaniCount      = Surah::where('type', 'Medinan')->count();

        $totalVerses      = Verse::count();

        $totalReciters    = Reciter::count();
        $activeReciters   = Reciter::active()->count();
        $inactiveReciters = $totalReciters - $activeReciters;

        $totalRecitations = Recitation::count();

        return [
            Stat::make('Surahs', $totalSurahs)
                ->description("{$makkiCount} Meccan · {$madaniCount} Medinan")
                ->descriptionIcon('heroicon-m-book-open', 'before')
                ->icon('heroicon-o-book-open')
                ->color('primary')
                ->chart([20, 40, 35, 55, 60, 75, 80, 90, 100, $totalSurahs]),

            Stat::make('Verses', number_format($totalVerses))
                ->description('Total Quranic verses')
                ->descriptionIcon('heroicon-m-document-text', 'before')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->chart([1000, 2000, 3200, 4000, 4800, 5400, 5800, 6100, 6200, $totalVerses]),

            Stat::make('Reciters', $totalReciters)
                ->description("{$activeReciters} active · {$inactiveReciters} inactive")
                ->descriptionIcon('heroicon-m-microphone', 'before')
                ->icon('heroicon-o-microphone')
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 6, 7, 8, 9, max($totalReciters, 1)]),

            Stat::make('Recitations', number_format($totalRecitations))
                ->description('Audio recordings available')
                ->descriptionIcon('heroicon-m-musical-note', 'before')
                ->icon('heroicon-o-musical-note')
                ->color('warning')
                ->chart([5, 15, 30, 60, 90, 130, 180, 220, 270, max($totalRecitations, 1)]),
        ];
    }
}
