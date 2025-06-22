<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCampaign extends ViewRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('view_public')
                ->label('View Public Page')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) => route('campaigns.show', $record->slug))
                ->openUrlInNewTab(),
            Actions\Action::make('approve')
                ->label('Approve Campaign')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'active',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ]);
                }),
            Actions\Action::make('suspend')
                ->label('Suspend Campaign')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => in_array($record->status, ['active', 'pending']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Suspension Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => 'suspended',
                        'admin_notes' => $data['reason'],
                    ]);
                }),
            Actions\Action::make('feature')
                ->label(fn ($record) => $record->is_featured ? 'Unfeature' : 'Feature')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured])),
            Actions\Action::make('mark_urgent')
                ->label(fn ($record) => $record->is_urgent ? 'Remove Urgent' : 'Mark Urgent')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->action(fn ($record) => $record->update(['is_urgent' => !$record->is_urgent])),
            Actions\DeleteAction::make(),
        ];
    }
}