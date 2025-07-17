<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawalsResource\Pages;
use App\Filament\Resources\WithdrawalsResource\RelationManagers;
use App\Models\Withdrawals;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WithdrawalsResource extends Resource
{
    protected static ?string $model = Withdrawals::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListWithdrawals::route('/'),
            'create' => Pages\CreateWithdrawals::route('/create'),
            'edit' => Pages\EditWithdrawals::route('/{record}/edit'),
        ];
    }
}
