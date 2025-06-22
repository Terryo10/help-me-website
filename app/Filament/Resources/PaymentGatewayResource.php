<?php

// app/Filament/Resources/PaymentGatewayResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayResource\Pages;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Gateway Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(PaymentGateway::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\TextInput::make('provider_class')
                            ->label('Provider Class')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('payment-gateways')
                            ->visibility('public'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\KeyValue::make('configuration')
                            ->label('Gateway Configuration')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Fee Structure')
                    ->schema([
                        Forms\Components\TextInput::make('fee_percentage')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->default(0),

                        Forms\Components\TextInput::make('fee_fixed')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'ZWL' => 'ZWL',
                            ])
                            ->default('USD')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Toggle::make('supports_refunds')
                            ->label('Supports Refunds')
                            ->default(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('fee_percentage')
                    ->label('Fee %')
                    ->formatStateUsing(fn (string $state): string => $state . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fee_fixed')
                    ->label('Fixed Fee')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('donations_count')
                    ->label('Donations')
                    ->counts('donations')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\IconColumn::make('supports_refunds')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('supports_refunds')
                    ->label('Supports Refunds'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentGateways::route('/'),
            'create' => Pages\CreatePaymentGateway::route('/create'),
            'edit' => Pages\EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}