<?php

namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDonation extends CreateRecord
{
    protected static string $resource = DonationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['donation_id'])) {
            $data['donation_id'] = 'DON-' . time() . '-' . rand(1000, 9999);
        }

        // Calculate net amount if not provided
        if (empty($data['net_amount'])) {
            $data['net_amount'] = $data['amount'] - ($data['fee_amount'] ?? 0);
        }

        return $data;
    }
}

