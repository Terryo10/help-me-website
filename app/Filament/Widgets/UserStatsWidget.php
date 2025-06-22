<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $individualUsers = User::where('user_type', 'individual')->count();
        $nonProfitUsers = User::where('user_type', 'non_profit')->count();
        $verifiedEmails = User::whereNotNull('email_verified_at')->count();
        $verifiedOrgs = User::whereHas('profile', function ($query) {
            $query->where('verification_status', 'verified');
        })->count();
        $pendingVerification = User::whereHas('profile', function ($query) {
            $query->where('verification_status', 'pending');
        })->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Individual Users', $individualUsers)
                ->description('Personal accounts')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),

            Stat::make('Non-Profit Organizations', $nonProfitUsers)
                ->description('Organization accounts')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Verified Emails', $verifiedEmails)
                ->description('Users with verified emails')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('success'),

            Stat::make('Verified Organizations', $verifiedOrgs)
                ->description('Verified non-profit organizations')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),

            Stat::make('Pending Verification', $pendingVerification)
                ->description('Organizations awaiting verification')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}