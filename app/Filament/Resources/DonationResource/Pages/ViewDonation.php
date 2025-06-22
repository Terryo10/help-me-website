<?php
namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDonation extends ViewRecord
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('view_campaign')
                ->label('View Campaign')
                ->icon('heroicon-o-megaphone')
                ->color('primary')
                ->url(fn ($record) => \App\Filament\Resources\CampaignResource::getUrl('view', ['record' => $record->campaign])),
            Actions\Action::make('send_receipt')
                ->label('Send Receipt')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'completed' && !$record->is_anonymous)
                ->requiresConfirmation()
                ->action(function ($record) {
                    // Implement receipt sending logic here
                    // Mail::to($record->donor_email)->send(new DonationReceipt($record));
                }),
        ];
    }
}
