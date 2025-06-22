<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CampaignResource;
use App\Models\Campaign;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentCampaignsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Campaigns';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Campaign::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->size(40)
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Creator'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'active',
                        'primary' => 'paused',
                        'secondary' => 'completed',
                        'danger' => ['suspended', 'rejected'],
                    ]),

                Tables\Columns\TextColumn::make('goal_amount')
                    ->money('USD'),

                Tables\Columns\TextColumn::make('raised_amount')
                    ->money('USD'),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn (string $state): string => number_format($state, 1) . '%')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Campaign $record): string => CampaignResource::getUrl('view', ['record' => $record])),
            ]);
    }
}