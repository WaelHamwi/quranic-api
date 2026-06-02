<?php

namespace App\Filament\Widgets;

use App\Models\Surah;
use Filament\Widgets\ChartWidget;

class SurahTypeWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected string $color = 'primary';

    protected ?string $heading = 'Surah Types';

    protected ?string $description = 'Distribution of Meccan vs Medinan surahs';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $meccan  = Surah::where('type', 'Meccan')->count();
        $medinan = Surah::where('type', 'Medinan')->count();
        $other   = Surah::whereNotIn('type', ['Meccan', 'Medinan'])->count();

        $labels = ['Meccan', 'Medinan'];
        $data   = [$meccan, $medinan];
        $colors = ['rgba(16,185,129,.8)', 'rgba(14,165,233,.8)'];
        $borders = ['#10b981', '#0ea5e9'];

        if ($other > 0) {
            $labels[] = 'Other';
            $data[]   = $other;
            $colors[]  = 'rgba(245,158,11,.8)';
            $borders[] = '#f59e0b';
        }

        return [
            'datasets' => [
                [
                    'data'                  => $data,
                    'backgroundColor'       => $colors,
                    'borderColor'           => $borders,
                    'borderWidth'           => 2,
                    'hoverOffset'           => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels'   => ['padding' => 20, 'usePointStyle' => true],
                ],
            ],
            'cutout' => '68%',
        ];
    }
}
