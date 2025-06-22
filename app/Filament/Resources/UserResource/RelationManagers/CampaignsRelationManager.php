<?php

// app/Filament/Resources/UserResource/RelationManagers/CampaignsRelationManager.php
namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CampaignsRelationManager extends RelationManager
{
    protected static string $relationship = 'campaigns';

    protected static ?string $recordTitleAttribute = 'title';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->size(50)
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30)
                    ->weight('bold')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'active',
                        'primary' => 'paused',
                        'secondary' => 'completed',
                        'danger' => ['suspended', 'rejected'],
                    ]),

                Tables\Columns\TextColumn::make('categories.name')
                    ->badge()
                    ->separator(',')
                    ->limit(30),

                Tables\Columns\TextColumn::make('goal_amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('raised_amount')
                    ->money('USD')
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('donations_count')
                    ->label('Donations')
                    ->counts('donations')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\IconColumn::make('is_urgent')
                    ->boolean()
                    ->label('Urgent'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                        'suspended' => 'Suspended',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('is_featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->toggle(),

                Tables\Filters\Filter::make('is_urgent')
                    ->query(fn (Builder $query): Builder => $query->where('is_urgent', true))
                    ->toggle(),

                Tables\Filters\Filter::make('progress_range')
                    ->form([
                        Forms\Components\Select::make('progress')
                            ->options([
                                '0-25' => '0-25%',
                                '26-50' => '26-50%',
                                '51-75' => '51-75%',
                                '76-100' => '76-100%',
                                '100+' => 'Over 100%',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['progress']) {
                            return $query;
                        }

                        return match ($data['progress']) {
                            '0-25' => $query->whereRaw('(raised_amount / goal_amount * 100) BETWEEN 0 AND 25'),
                            '26-50' => $query->whereRaw('(raised_amount / goal_amount * 100) BETWEEN 26 AND 50'),
                            '51-75' => $query->whereRaw('(raised_amount / goal_amount * 100) BETWEEN 51 AND 75'),
                            '76-100' => $query->whereRaw('(raised_amount / goal_amount * 100) BETWEEN 76 AND 100'),
                            '100+' => $query->whereRaw('(raised_amount / goal_amount * 100) > 100'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => \App\Filament\Resources\CampaignResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\CampaignResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_public')
                    ->icon('heroicon-o-globe-alt')
                    ->color('primary')
                    ->url(fn ($record) => route('campaigns.show', $record->slug))
                    ->openUrlInNewTab()
                    ->label('View Public'),

                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'active',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                    }),

                Tables\Actions\Action::make('suspend')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['active', 'pending']))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Suspension Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'suspended',
                            'admin_notes' => $data['reason'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'active',
                                        'approved_at' => now(),
                                        'approved_by' => auth()->id(),
                                    ]);
                                }
                            });
                        }),

                    Tables\Actions\BulkAction::make('feature_selected')
                        ->label('Feature Selected')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => true]));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

// app/Filament/Resources/UserResource/RelationManagers/DonationsRelationManager.php
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

    protected static ?string $recordTitleAttribute = 'donation_id';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('donation_id')
            ->columns([
                Tables\Columns\TextColumn::make('donation_id')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('campaign.title')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

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

                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

                Tables\Filters\SelectFilter::make('payment_gateway_id')
                    ->relationship('paymentGateway', 'name')
                    ->label('Payment Gateway'),

                Tables\Filters\TernaryFilter::make('is_anonymous')
                    ->label('Anonymous'),

                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('amount_to')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_campaign')
                    ->icon('heroicon-o-megaphone')
                    ->color('primary')
                    ->url(fn ($record) => \App\Filament\Resources\CampaignResource::getUrl('view', ['record' => $record->campaign]))
                    ->openUrlInNewTab()
                    ->label('View Campaign'),

                Tables\Actions\Action::make('view_donation')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => \App\Filament\Resources\DonationResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->label('View Details'),

                Tables\Actions\Action::make('send_receipt')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'completed' && !$record->is_anonymous && $record->donor_email)
                    ->requiresConfirmation()
                    ->modalHeading('Send Receipt')
                    ->modalDescription('Send a donation receipt to the donor?')
                    ->action(function ($record) {
                        // Implement receipt sending logic here
                        // Mail::to($record->donor_email)->send(new DonationReceipt($record));
                    }),

                Tables\Actions\Action::make('refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'completed')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('refund_reason')
                            ->label('Refund Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'refunded',
                            'metadata' => array_merge($record->metadata ?? [], [
                                'refund_reason' => $data['refund_reason'],
                                'refunded_at' => now()->toISOString(),
                                'refunded_by' => auth()->id(),
                            ]),
                        ]);
                        // Decrease campaign raised amount
                        $record->campaign->decrement('raised_amount', $record->amount);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('send_receipts')
                        ->label('Send Receipts')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if ($record->status === 'completed' && !$record->is_anonymous && $record->donor_email) {
                                    // Implement bulk receipt sending logic
                                    // Mail::to($record->donor_email)->send(new DonationReceipt($record));
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}