<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use App\Models\PaymentGateway;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DonationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDonations = Donation::count();
        $completedDonations = Donation::where('status', 'completed')->count();
        $pendingDonations = Donation::where('status', 'pending')->count();
        $totalAmount = Donation::where('status', 'completed')->sum('amount');
        $averageDonation = $completedDonations > 0 ? $totalAmount / $completedDonations : 0;
        $monthlyDonations = Donation::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('Total Donations', $totalDonations)
                ->description('All donation attempts')
                ->descriptionIcon('heroicon-m-heart')
                ->color('primary'),

            Stat::make('Completed Donations', $completedDonations)
                ->description('Successfully processed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending Donations', $pendingDonations)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Amount', '$' . number_format($totalAmount, 2))
                ->description('Successfully donated')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Average Donation', '$' . number_format($averageDonation, 2))
                ->description('Per completed donation')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('This Month', '$' . number_format($monthlyDonations, 2))
                ->description('Donations this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}