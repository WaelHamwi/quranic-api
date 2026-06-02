<?php

namespace App\Filament\Widgets;

use App\Models\Reciter;
use Filament\Widgets\ChartWidget;

class ReciterRecitationsWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected string $color = 'success';

    protected ?string $heading = 'Recitations per Reciter';

    protected ?string $description = 'Number of audio recordings per reciter';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $reciters = Reciter::withCount('recitations')
            ->orderByDesc('recitations_count')
            ->limit(8)
            ->get();

        $labels = $reciters->map(fn ($r) => $r->getTranslation('name', 'en'))->toArray();
        $counts = $reciters->pluck('recitations_count')->toArray();

        $backgroundColors = [
            'rgba(16,185,129,.75)',
            'rgba(13,148,136,.75)',
            'rgba(14,165,233,.75)',
            'rgba(16,185,129,.55)',
            'rgba(13,148,136,.55)',
            'rgba(14,165,233,.55)',
            'rgba(16,185,129,.4)',
            'rgba(13,148,136,.4)',
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'Recitations',
                    'data'            => $counts,
                    'backgroundColor' => \array_slice($backgroundColors, 0, \count($counts)),
                    'borderColor'     => array_map(
                        fn ($c) => str_replace('.75)', '1)', str_replace('.55)', '1)', str_replace('.4)', '1)', $c))),
                        \array_slice($backgroundColors, 0, \count($counts))
                    ),
                    'borderWidth'     => 1,
                    'borderRadius'    => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1],
                    'grid'        => ['drawBorder' => false],
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }
}
