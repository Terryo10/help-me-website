<?php
// app/Filament/Resources/UserResource/Pages/ViewUser.php
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\User;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('send_email')
                ->label('Send Email')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\TextInput::make('subject')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\RichEditor::make('message')
                        ->required(),
                ])
                ->action(function (array $data, User $record) {
                    // Implement email sending logic here
                    // Mail::to($record->email)->send(new AdminEmail($data['subject'], $data['message']));
                }),
            Actions\Action::make('login_as_user')
                ->label('Login as User')
                ->icon('heroicon-o-user-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Login as this user?')
                ->modalDescription('This will log you in as this user. Are you sure?')
                ->action(function (User $record) {
                    auth()->login($record);
                    return redirect()->route('dashboard');
                })
                ->visible(fn (User $record) => $record->id !== auth()->id()),
            Actions\Action::make('verify_email')
                ->label('Verify Email')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->visible(fn (User $record) => !$record->hasVerifiedEmail())
                ->requiresConfirmation()
                ->action(function (User $record) {
                    $record->markEmailAsVerified();
                }),
            Actions\Action::make('verify_organization')
                ->label('Verify Organization')
                ->icon('heroicon-o-shield-check')
                ->color('success')
                ->visible(fn (User $record) => $record->user_type === 'non_profit' && $record->profile?->verification_status !== 'verified')
                ->form([
                    \Filament\Forms\Components\Textarea::make('verification_notes')
                        ->label('Verification Notes')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (User $record, array $data) {
                    $record->profile()->updateOrCreate([], [
                        'verification_status' => 'verified',
                        'verification_notes' => $data['verification_notes'],
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ]);
                }),
            Actions\Action::make('suspend')
                ->label('Suspend User')
                ->icon('heroicon-o-no-symbol')
                ->color('warning')
                ->visible(fn (User $record) => $record->status === 'active')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Suspension Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (User $record, array $data) {
                    $record->update(['status' => 'suspended']);
                    // Log suspension reason if needed
                }),
            Actions\Action::make('ban')
                ->label('Ban User')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (User $record) => $record->status !== 'banned')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Ban Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (User $record, array $data) {
                    $record->update(['status' => 'banned']);
                    // Log ban reason if needed
                }),
            Actions\Action::make('activate')
                ->label('Activate User')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (User $record) => in_array($record->status, ['suspended', 'inactive', 'banned']))
                ->requiresConfirmation()
                ->action(fn (User $record) => $record->update(['status' => 'active'])),
        ];
    }
}
