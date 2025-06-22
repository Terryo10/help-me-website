<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CampaignStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $pendingCampaigns = Campaign::where('status', 'pending')->count();
        $completedCampaigns = Campaign::where('status', 'completed')->count();
        $totalRaised = Campaign::sum('raised_amount');
        $totalGoal = Campaign::sum('goal_amount');
        $averageProgress = $totalGoal > 0 ? ($totalRaised / $totalGoal) * 100 : 0;

        return [
            Stat::make('Total Campaigns', $totalCampaigns)
                ->description('All campaigns in the system')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),

            Stat::make('Active Campaigns', $activeCampaigns)
                ->description('Currently active campaigns')
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),

            Stat::make('Pending Review', $pendingCampaigns)
                ->description('Campaigns awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Raised', '$' . number_format($totalRaised, 2))
                ->description('Across all campaigns')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Average Progress', number_format($averageProgress, 1) . '%')
                ->description('Overall funding progress')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($averageProgress >= 75 ? 'success' : ($averageProgress >= 50 ? 'warning' : 'danger')),

            Stat::make('Completed', $completedCampaigns)
                ->description('Successfully completed campaigns')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}