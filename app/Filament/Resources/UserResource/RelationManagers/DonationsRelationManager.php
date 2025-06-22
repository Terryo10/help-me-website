<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DonationsRelationManager extends RelationManager
{
    protected static string $relationship = 'donations';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('donation_id')
            ->columns([
                Tables\Columns\TextColumn::make('donation_id')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('campaign.title')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')  
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => ['failed', 'cancelled'],
                        'secondary' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('paymentGateway.name')
                    ->label('Gateway'),
                Tables\Columns\IconColumn::make('is_anonymous')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_campaign')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => \App\Filament\Resources\CampaignResource::getUrl('view', ['record' => $record->campaign])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}