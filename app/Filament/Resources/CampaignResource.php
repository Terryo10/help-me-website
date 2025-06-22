<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Colors\Color;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Campaign Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Campaign Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                if ($context === 'create') {
                                    $set('slug', \Illuminate\Support\Str::slug($state) . '-' . time());
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Campaign::class, 'slug', ignoreRecord: true),

                        Forms\Components\Select::make('user_id')
                            ->label('Campaign Creator')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\RichEditor::make('story')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('goal_amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Forms\Components\TextInput::make('raised_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(0)
                            ->readOnly(),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'ZWL' => 'ZWL',
                            ])
                            ->default('USD')
                            ->required(),

                        Forms\Components\TextInput::make('minimum_donation')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->suffix('$'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Campaign Settings')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending Review',
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'completed' => 'Completed',
                                'suspended' => 'Suspended',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('start_date'),

                        Forms\Components\DatePicker::make('end_date'),

                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Campaign'),

                        Forms\Components\Toggle::make('is_urgent')
                            ->label('Urgent Campaign'),

                        Forms\Components\Toggle::make('allow_anonymous_donations')
                            ->label('Allow Anonymous Donations')
                            ->default(true),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->directory('campaigns')
                            ->visibility('public')
                            ->maxSize(2048),

                        Forms\Components\FileUpload::make('gallery')
                            ->image()
                            ->multiple()
                            ->directory('campaigns/gallery')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->maxFiles(5),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Admin Notes')
                    ->schema([
                        Forms\Components\Textarea::make('admin_notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('approved_by')
                            ->relationship('approvedBy', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('approved_at'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->size(60)
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->limit(30),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('categories.name')
                    ->badge()
                    ->separator(',')
                    ->limit(20),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_urgent')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('donation_count')
                    ->label('Donations')
                    ->counts('donations')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending Review',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                        'suspended' => 'Suspended',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),

                Filter::make('is_featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->toggle(),

                Filter::make('is_urgent')
                    ->query(fn (Builder $query): Builder => $query->where('is_urgent', true))
                    ->toggle(),

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

                Filter::make('funding_progress')
                    ->form([
                        Forms\Components\Select::make('progress_range')
                            ->options([
                                '0-25' => '0-25%',
                                '26-50' => '26-50%',
                                '51-75' => '51-75%',
                                '76-100' => '76-100%',
                                '100+' => 'Over 100%',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['progress_range']) {
                            return $query;
                        }

                        return match ($data['progress_range']) {
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => $record->status === 'pending')
                    ->action(function (Campaign $record): void {
                        $record->update([
                            'status' => 'active',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                    }),

                Tables\Actions\Action::make('suspend')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['active', 'pending']))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Suspension Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Campaign $record, array $data): void {
                        $record->update([
                            'status' => 'suspended',
                            'admin_notes' => $data['reason'],
                        ]);
                    }),

                Tables\Actions\Action::make('feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn (Campaign $record) => $record->update(['is_featured' => !$record->is_featured]))
                    ->label(fn (Campaign $record): string => $record->is_featured ? 'Unfeature' : 'Feature'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each(function (Campaign $record): void {
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
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each(fn (Campaign $record) => $record->update(['is_featured' => true]));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Campaign Overview')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('title')
                                            ->weight(FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        Infolists\Components\TextEntry::make('description'),
                                        Infolists\Components\TextEntry::make('user.name')
                                            ->label('Created by'),
                                        Infolists\Components\TextEntry::make('location')
                                            ->icon('heroicon-m-map-pin'),
                                    ]),
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'pending' => 'warning',
                                                'active' => 'success',
                                                'paused' => 'primary',
                                                'completed' => 'secondary',
                                                'suspended', 'rejected' => 'danger',
                                            }),
                                        Infolists\Components\TextEntry::make('categories.name')
                                            ->badge()
                                            ->separator(','),
                                        Infolists\Components\IconEntry::make('is_featured')
                                            ->label('Featured')
                                            ->boolean(),
                                        Infolists\Components\IconEntry::make('is_urgent')
                                            ->label('Urgent')
                                            ->boolean(),
                                    ]),
                                ]),
                            Infolists\Components\ImageEntry::make('featured_image')
                                ->hiddenLabel()
                                ->grow(false),
                        ])->from('lg'),
                    ]),

                Infolists\Components\Section::make('Financial Information')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('goal_amount')
                                    ->money('USD')
                                    ->label('Goal'),
                                Infolists\Components\TextEntry::make('raised_amount')
                                    ->money('USD')
                                    ->label('Raised'),
                                Infolists\Components\TextEntry::make('progress_percentage')
                                    ->label('Progress')
                                    ->formatStateUsing(fn (string $state): string => number_format($state, 1) . '%'),
                                Infolists\Components\TextEntry::make('minimum_donation')
                                    ->money('USD')
                                    ->label('Min. Donation'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Campaign Story')
                    ->schema([
                        Infolists\Components\TextEntry::make('story')
                            ->prose()
                            ->hiddenLabel()
                            ->html(),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('view_count')
                                    ->label('Views'),
                                Infolists\Components\TextEntry::make('share_count')
                                    ->label('Shares'),
                                Infolists\Components\TextEntry::make('donation_count')
                                    ->label('Donations'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Admin Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('admin_notes')
                                    ->label('Admin Notes')
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('approvedBy.name')
                                    ->label('Approved By'),
                                Infolists\Components\TextEntry::make('approved_at')
                                    ->label('Approved At')
                                    ->dateTime(),
                            ]),
                    ])
                    ->visible(fn (Campaign $record): bool => $record->admin_notes || $record->approved_by),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DonationsRelationManager::class,
            RelationManagers\UpdatesRelationManager::class,
            RelationManagers\MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
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