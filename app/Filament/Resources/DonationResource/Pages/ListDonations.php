<?php

// app/Filament/Resources/DonationResource/Pages/ListDonations.php
namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDonations extends ListRecords
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Donations'),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\Donation::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\Donation::where('status', 'completed')->count())
                ->badgeColor('success'),
            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed'))
                ->badge(fn () => \App\Models\Donation::where('status', 'failed')->count())
                ->badgeColor('danger'),
            'refunded' => Tab::make('Refunded')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'refunded'))
                ->badge(fn () => \App\Models\Donation::where('status', 'refunded')->count())
                ->badgeColor('secondary'),
            'anonymous' => Tab::make('Anonymous')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_anonymous', true))
                ->badge(fn () => \App\Models\Donation::where('is_anonymous', true)->count())
                ->badgeColor('gray'),
        ];
    }
}