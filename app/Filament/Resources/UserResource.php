<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
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
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Select::make('user_type')
                            ->options([
                                'individual' => 'Individual',
                                'non_profit' => 'Non-Profit Organization',
                            ])
                            ->default('individual')
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                                'banned' => 'Banned',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\DatePicker::make('date_of_birth'),

                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                                'prefer_not_to_say' => 'Prefer not to say',
                            ]),

                        Forms\Components\TextInput::make('country')
                            ->maxLength(255)
                            ->default('Zimbabwe'),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Account Settings')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At'),

                        Forms\Components\DateTimePicker::make('phone_verified_at')
                            ->label('Phone Verified At'),

                        Forms\Components\Toggle::make('email_notifications')
                            ->label('Email Notifications')
                            ->default(true),

                        Forms\Components\Toggle::make('sms_notifications')
                            ->label('SMS Notifications')
                            ->default(true),

                        Forms\Components\DateTimePicker::make('last_login_at')
                            ->label('Last Login')
                            ->readOnly(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->directory('avatars')
                            ->visibility('public')
                            ->maxSize(2048),

                        Forms\Components\Textarea::make('bio')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\KeyValue::make('social_links')
                            ->label('Social Media Links')
                            ->keyLabel('Platform')
                            ->valueLabel('URL')
                            ->reorderable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Non-Profit Information')
                    ->schema([
                        Forms\Components\TextInput::make('profile.organization_name')
                            ->label('Organization Name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('profile.registration_number')
                            ->label('Registration Number')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('profile.tax_number')
                            ->label('Tax Number')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('profile.mission_statement')
                            ->label('Mission Statement')
                            ->rows(3),

                        Forms\Components\TextInput::make('profile.founded_year')
                            ->label('Founded Year')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y')),

                        Forms\Components\TextInput::make('profile.website')
                            ->label('Organization Website')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('profile.address')
                            ->label('Address')
                            ->rows(2),

                        Forms\Components\Select::make('profile.verification_status')
                            ->label('Verification Status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending'),

                        Forms\Components\Textarea::make('profile.verification_notes')
                            ->label('Verification Notes')
                            ->rows(3),

                        Forms\Components\DateTimePicker::make('profile.verified_at')
                            ->label('Verified At'),

                        Forms\Components\Select::make('profile.verified_by')
                            ->label('Verified By')
                            ->relationship('profile.verifiedBy', 'name')
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->visible(fn (Forms\Get $get): bool => $get('user_type') === 'non_profit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('user_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'individual',
                        'success' => 'non_profit',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => 'Individual',
                        'non_profit' => 'Non-Profit',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                        'warning' => 'suspended',
                        'danger' => 'banned',
                    ]),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('profile.verification_status')
                    ->label('Org. Verified')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'N/A')
                    ->visible(fn (): bool => User::where('user_type', 'non_profit')->exists()),

                Tables\Columns\TextColumn::make('campaigns_count')
                    ->label('Campaigns')
                    ->counts('campaigns')
                    ->sortable(),

                Tables\Columns\TextColumn::make('donations_count')
                    ->label('Donations')
                    ->counts('donations')
                    ->sortable(),

                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        'banned' => 'Banned',
                    ]),

                SelectFilter::make('user_type')
                    ->label('User Type')
                    ->options([
                        'individual' => 'Individual',
                        'non_profit' => 'Non-Profit',
                    ]),

                Filter::make('email_verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->toggle(),

                Filter::make('phone_verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('phone_verified_at'))
                    ->toggle(),

                SelectFilter::make('verification_status')
                    ->relationship('profile', 'verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Organization Verification'),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Registered From'),
                        DatePicker::make('created_until')
                            ->label('Registered Until'),
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

                Filter::make('last_login')
                    ->form([
                        DatePicker::make('last_login_from')
                            ->label('Last Login From'),
                        DatePicker::make('last_login_until')
                            ->label('Last Login Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['last_login_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('last_login_at', '>=', $date),
                            )
                            ->when(
                                $data['last_login_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('last_login_at', '<=', $date),
                            );
                    }),

                Filter::make('has_campaigns')
                    ->query(fn (Builder $query): Builder => $query->whereHas('campaigns'))
                    ->toggle(),

                Filter::make('has_donations')
                    ->query(fn (Builder $query): Builder => $query->whereHas('donations'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('verify_email')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->visible(fn (User $record): bool => !$record->hasVerifiedEmail())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->markEmailAsVerified();
                    }),

                Tables\Actions\Action::make('verify_organization')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->user_type === 'non_profit' && $record->profile?->verification_status !== 'verified')
                    ->form([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->profile()->updateOrCreate([], [
                            'verification_status' => 'verified',
                            'verification_notes' => $data['verification_notes'],
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                    }),

                Tables\Actions\Action::make('suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->visible(fn (User $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Suspension Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update(['status' => 'suspended']);
                        // You might want to log the suspension reason
                    }),

                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => in_array($record->status, ['suspended', 'inactive']))
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['status' => 'active'])),

                Tables\Actions\Action::make('ban')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->status !== 'banned')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Ban Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update(['status' => 'banned']);
                        // You might want to log the ban reason
                    }),

                Tables\Actions\Action::make('send_verification_email')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->visible(fn (User $record): bool => !$record->hasVerifiedEmail())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->sendEmailVerificationNotification();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('verify_emails')
                        ->label('Verify Emails')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each(function (User $record): void {
                                if (!$record->hasVerifiedEmail()) {
                                    $record->markEmailAsVerified();
                                }
                            });
                        }),

                    Tables\Actions\BulkAction::make('suspend_users')
                        ->label('Suspend Users')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Suspension Reason')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $records->each(fn (User $record) => $record->update(['status' => 'suspended']));
                        }),

                    Tables\Actions\BulkAction::make('activate_users')
                        ->label('Activate Users')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each(fn (User $record) => $record->update(['status' => 'active']));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Profile')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('name')
                                            ->weight(FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        Infolists\Components\TextEntry::make('email')
                                            ->icon('heroicon-m-envelope')
                                            ->copyable(),
                                        Infolists\Components\TextEntry::make('phone')
                                            ->icon('heroicon-m-phone'),
                                        Infolists\Components\TextEntry::make('website')
                                            ->icon('heroicon-m-globe-alt')
                                            ->url(fn (?string $state): ?string => $state),
                                    ]),
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'active' => 'success',
                                                'inactive' => 'gray',
                                                'suspended' => 'warning',
                                                'banned' => 'danger',
                                            }),
                                        Infolists\Components\TextEntry::make('user_type')
                                            ->label('User Type')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'individual' => 'primary',
                                                'non_profit' => 'success',
                                            })
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'individual' => 'Individual',
                                                'non_profit' => 'Non-Profit',
                                                default => $state,
                                            }),
                                        Infolists\Components\IconEntry::make('email_verified_at')
                                            ->label('Email Verified')
                                            ->boolean(),
                                        Infolists\Components\IconEntry::make('phone_verified_at')
                                            ->label('Phone Verified')
                                            ->boolean(),
                                    ]),
                                ]),
                            Infolists\Components\ImageEntry::make('avatar')
                                ->hiddenLabel()
                                ->circular()
                                ->grow(false),
                        ])->from('lg'),
                    ]),

                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('date_of_birth')
                                    ->date(),
                                Infolists\Components\TextEntry::make('gender')
                                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst(str_replace('_', ' ', $state)) : 'Not specified'),
                                Infolists\Components\TextEntry::make('country'),
                                Infolists\Components\TextEntry::make('city'),
                            ]),
                        Infolists\Components\TextEntry::make('bio')
                            ->columnSpanFull()
                            ->prose(),
                    ]),

                Infolists\Components\Section::make('Organization Details')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('profile.organization_name')
                                    ->label('Organization Name'),
                                Infolists\Components\TextEntry::make('profile.registration_number')
                                    ->label('Registration Number'),
                                Infolists\Components\TextEntry::make('profile.tax_number')
                                    ->label('Tax Number'),
                                Infolists\Components\TextEntry::make('profile.founded_year')
                                    ->label('Founded Year'),
                                Infolists\Components\TextEntry::make('profile.verification_status')
                                    ->label('Verification Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('profile.verified_at')
                                    ->label('Verified At')
                                    ->dateTime(),
                            ]),
                        Infolists\Components\TextEntry::make('profile.mission_statement')
                            ->label('Mission Statement')
                            ->columnSpanFull()
                            ->prose(),
                        Infolists\Components\TextEntry::make('profile.address')
                            ->label('Address')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('profile.verification_notes')
                            ->label('Verification Notes')
                            ->columnSpanFull()
                            ->visible(fn ($record): bool => $record->profile?->verification_notes),
                    ])
                    ->visible(fn ($record): bool => $record->user_type === 'non_profit'),

                Infolists\Components\Section::make('Account Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('campaigns_count')
                                    ->label('Total Campaigns')
                                    ->numeric(),
                                Infolists\Components\TextEntry::make('donations_count')
                                    ->label('Total Donations')
                                    ->numeric(),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Member Since')
                                    ->date(),
                                Infolists\Components\TextEntry::make('last_login_at')
                                    ->label('Last Login')
                                    ->dateTime(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Preferences')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\IconEntry::make('email_notifications')
                                    ->label('Email Notifications')
                                    ->boolean(),
                                Infolists\Components\IconEntry::make('sms_notifications')
                                    ->label('SMS Notifications')
                                    ->boolean(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Social Links')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('social_links')
                            ->hiddenLabel(),
                    ])
                    ->visible(fn ($record): bool => !empty($record->social_links)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CampaignsRelationManager::class,
            RelationManagers\DonationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pendingVerifications = static::getModel()::whereHas('profile', function ($query) {
            $query->where('verification_status', 'pending');
        })->count();

        return $pendingVerifications > 0 ? (string) $pendingVerifications : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() ? 'warning' : null;
    }
}