<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Update Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('images')
                            ->image()
                            ->multiple()
                            ->directory('campaign-updates')
                            ->visibility('public')
                            ->maxFiles(5)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('notify_donors')
                            ->label('Notify all donors about this update')
                            ->helperText('Send email notification to all campaign donors'),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish at')
                            ->helperText('Leave empty to save as draft'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('content')
                    ->html()
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = strip_tags($column->getState());
                        return strlen($state) > 100 ? $state : null;
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->published_at ? 'Published' : 'Draft')
                    ->colors([
                        'success' => 'Published',
                        'warning' => 'Draft',
                    ]),

                Tables\Columns\IconColumn::make('notify_donors')
                    ->boolean()
                    ->label('Notify'),

                Tables\Columns\TextColumn::make('images')
                    ->getStateUsing(fn ($record) => is_array($record->images) ? count($record->images) : 0)
                    ->label('Images')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('published')
                    ->label('Published Status')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('published_at'),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                    ),

                Tables\Filters\Filter::make('has_images')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('images'))
                    ->toggle(),

                Tables\Filters\TernaryFilter::make('notify_donors')
                    ->label('Notifies Donors'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['campaign_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn ($record) => !$record->published_at)
                    ->requiresConfirmation()
                    ->modalHeading('Publish Update')
                    ->modalDescription('Are you sure you want to publish this update? This will make it visible to all visitors.')
                    ->action(function ($record) {
                        $record->update(['published_at' => now()]);
                        
                        // If notify_donors is true, you can add logic here to send notifications
                        if ($record->notify_donors) {
                            // Implement notification logic
                            // NotifyDonorsJob::dispatch($record);
                        }
                    }),
                Tables\Actions\Action::make('unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn ($record) => $record->published_at)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['published_at' => null])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish_selected')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if (!$record->published_at) {
                                    $record->update(['published_at' => now()]);
                                    
                                    if ($record->notify_donors) {
                                        // Implement notification logic
                                        // NotifyDonorsJob::dispatch($record);
                                    }
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('unpublish_selected')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($record) => $record->update(['published_at' => null]));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}