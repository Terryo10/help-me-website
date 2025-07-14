<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsLetterResource\Pages;
use App\Filament\Resources\NewsLetterResource\RelationManagers;
use App\Models\NewsLetter;
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

class NewsLetterResource extends Resource
{
    protected static ?string $model = NewsLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_subscribed')
                    ->label('Is Subscribed')
                    ->default(true),

                Forms\Components\DateTimePicker::make('subscribed_at')
                    ->label('Subscribed At'),

                Forms\Components\DateTimePicker::make('unsubscribed_at')
                    ->label('Unsubscribed At'),

                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->maxLength(45),

                Forms\Components\Textarea::make('user_agent')
                    ->label('User Agent')
                    ->rows(2),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Created At')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('updated_at')
                    ->label('Updated At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_subscribed')
                    ->label('Is Subscribed')
                    ->boolean(),

                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label('Subscribed At')
                    ->dateTime()
                    ->sortable(),

                 Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->label('Unsubscribed At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('NewsLetter Overview')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    // Infolists\Components\Group::make([
                                    //     Infolists\Components\TextEntry::make('subject')
                                    //         ->weight(FontWeight::Bold)
                                    //         ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    //     Infolists\Components\TextEntry::make('email')
                                    //         ->prose()
                                    //         ->hiddenLabel()
                                    //         ->html(),
                                    //     Infolists\Components\TextEntry::make('sent_at')
                                    //         ->dateTime(),
                                    // ]),
                                ]),
                        ])->from('lg'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsLetters::route('/'),
            'create' => Pages\CreateNewsLetter::route('/create'),
            'view' => Pages\ViewNewsLetter::route('/{record}'),
            'edit' => Pages\EditNewsLetter::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 0 ? 'primary' : null;
    }
}
