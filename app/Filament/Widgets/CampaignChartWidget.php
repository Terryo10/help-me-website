<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CampaignChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Campaigns Created';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(Campaign::class)
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Campaigns created',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}