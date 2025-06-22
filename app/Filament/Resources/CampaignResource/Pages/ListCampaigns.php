<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Campaigns'),
            'pending' => Tab::make('Pending Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\Campaign::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => \App\Models\Campaign::where('status', 'active')->count())
                ->badgeColor('success'),
            'featured' => Tab::make('Featured')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->badge(fn () => \App\Models\Campaign::where('is_featured', true)->count())
                ->badgeColor('primary'),
            'urgent' => Tab::make('Urgent')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_urgent', true))
                ->badge(fn () => \App\Models\Campaign::where('is_urgent', true)->count())
                ->badgeColor('danger'),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\Campaign::where('status', 'completed')->count())
                ->badgeColor('gray'),
            'suspended' => Tab::make('Suspended')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'suspended'))
                ->badge(fn () => \App\Models\Campaign::where('status', 'suspended')->count())
                ->badgeColor('danger'),
        ];
    }
}
