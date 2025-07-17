<?php

namespace App\Filament\Resources\WithdrawalsResource\Pages;

use App\Filament\Resources\WithdrawalsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWithdrawals extends EditRecord
{
    protected static string $resource = WithdrawalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
