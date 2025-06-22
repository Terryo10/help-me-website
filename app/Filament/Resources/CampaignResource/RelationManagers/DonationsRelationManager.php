<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Donation Details')
                    ->schema([
                        Forms\Components\TextInput::make('donation_id')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => 'DON-' . time() . '-' . rand(1000, 9999)),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('payment_gateway_id')
                            ->relationship('paymentGateway', 'name')
                            ->required(),

                        Forms\Components\TextInput::make('donor_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('donor_email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('donor_phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Forms\Components\TextInput::make('fee_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('net_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'ZWL' => 'ZWL',
                            ])
                            ->default('USD')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Toggle::make('is_anonymous')
                            ->label('Anonymous Donation'),

                        Forms\Components\Toggle::make('show_comment_publicly')
                            ->label('Show Comment Publicly'),

                        Forms\Components\Textarea::make('comment')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('donation_id')
            ->columns([
                Tables\Columns\TextColumn::make('donation_id')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('donor_display_name')
                    ->label('Donor')
                    ->searchable(['donor_name', 'donor_email']),

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

                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['campaign_id'] = $this->ownerRecord->id;
                        if (empty($data['net_amount'])) {
                            $data['net_amount'] = $data['amount'] - ($data['fee_amount'] ?? 0);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        // Update campaign raised amount
                        $record->campaign->increment('raised_amount', $record->amount);
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'completed',
                                        'completed_at' => now(),
                                    ]);
                                    $record->campaign->increment('raised_amount', $record->amount);
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}