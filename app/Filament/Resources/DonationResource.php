<?php
namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Donation Details')
                    ->schema([
                        Forms\Components\TextInput::make('donation_id')
                            ->required()
                            ->unique(Donation::class, 'donation_id', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('campaign_id')
                            ->relationship('campaign', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('payment_gateway_id')
                            ->relationship('paymentGateway', 'name')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Donor Information')
                    ->schema([
                        Forms\Components\TextInput::make('donor_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('donor_email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('donor_phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_anonymous')
                            ->label('Anonymous Donation'),
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

                        Forms\Components\DateTimePicker::make('completed_at'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('show_comment_publicly')
                            ->label('Show Comment Publicly'),

                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(255),

                        Forms\Components\KeyValue::make('payment_data')
                            ->label('Payment Gateway Data'),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Additional Metadata'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('donation_id')
                    ->searchable()
                    ->copyable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('campaign.title')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

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
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_gateway_id')
                    ->relationship('paymentGateway', 'name')
                    ->label('Payment Gateway'),

                Filter::make('is_anonymous')
                    ->query(fn (Builder $query): Builder => $query->where('is_anonymous', true))
                    ->toggle(),

                Filter::make('amount')
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

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Donation Overview')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('donation_id')
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),
                                        Infolists\Components\TextEntry::make('campaign.title')
                                            ->label('Campaign'),
                                        Infolists\Components\TextEntry::make('donor_display_name')
                                            ->label('Donor'),
                                        Infolists\Components\TextEntry::make('paymentGateway.name')
                                            ->label('Payment Gateway'),
                                    ]),
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'completed' => 'success',
                                                'failed', 'cancelled' => 'danger',
                                                'refunded' => 'secondary',
                                            }),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->money('USD'),
                                        Infolists\Components\TextEntry::make('fee_amount')
                                            ->money('USD'),
                                        Infolists\Components\TextEntry::make('net_amount')
                                            ->money('USD'),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ]),

                Infolists\Components\Section::make('Donor Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('donor_name'),
                                Infolists\Components\TextEntry::make('donor_email')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('donor_phone'),
                                Infolists\Components\IconEntry::make('is_anonymous')
                                    ->boolean(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Payment Details')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('payment_reference'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('completed_at')
                                    ->dateTime(),
                            ]),
                        Infolists\Components\KeyValueEntry::make('payment_data')
                            ->label('Payment Gateway Data')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Comment')
                    ->schema([
                        Infolists\Components\TextEntry::make('comment')
                            ->prose()
                            ->hiddenLabel(),
                    ])
                    ->visible(fn ($record): bool => !empty($record->comment)),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('metadata')
                            ->hiddenLabel(),
                    ])
                    ->visible(fn ($record): bool => !empty($record->metadata)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'view' => Pages\ViewDonation::route('/{record}'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : null;
    }
}