<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $recordTitleAttribute = 'original_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Media Information')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'image' => 'Image',
                                'video' => 'Video',
                                'document' => 'Document',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('original_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Display Name'),

                        Forms\Components\FileUpload::make('path')
                            ->required()
                            ->directory('campaign-media')
                            ->visibility('public')
                            ->maxSize(10240) // 10MB
                            ->acceptedFileTypes(function (Forms\Get $get) {
                                return match ($get('type')) {
                                    'image' => ['image/*'],
                                    'video' => ['video/*'],
                                    'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                                    default => ['*/*'],
                                };
                            })
                            ->label('File'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Media'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Technical Details')
                    ->schema([
                        Forms\Components\TextInput::make('mime_type')
                            ->maxLength(255)
                            ->readOnly(),

                        Forms\Components\TextInput::make('size')
                            ->numeric()
                            ->readOnly()
                            ->formatStateUsing(function ($state) {
                                if (!$state) return null;
                                
                                $units = ['B', 'KB', 'MB', 'GB'];
                                $bytes = $state;
                                
                                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                                    $bytes /= 1024;
                                }
                                
                                return round($bytes, 2) . ' ' . $units[$i];
                            }),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Additional Metadata')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (string $context): bool => $context === 'edit'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->size(60)
                    ->visibility('public')
                    ->label('Preview'),

                Tables\Columns\TextColumn::make('original_name')
                    ->searchable()
                    ->weight('bold')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'image',
                        'success' => 'video',
                        'warning' => 'document',
                    ]),

                Tables\Columns\TextColumn::make('mime_type')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Size')
                    ->getStateUsing(function ($record) {
                        if (!$record->size) return 'Unknown';
                        
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $bytes = $record->size;
                        
                        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                            $bytes /= 1024;
                        }
                        
                        return round($bytes, 2) . ' ' . $units[$i];
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'document' => 'Document',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\Filter::make('size_range')
                    ->form([
                        Forms\Components\TextInput::make('size_from')
                            ->numeric()
                            ->label('Min Size (MB)'),
                        Forms\Components\TextInput::make('size_to')
                            ->numeric()
                            ->label('Max Size (MB)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['size_from'],
                                fn (Builder $query, $size): Builder => $query->where('size', '>=', $size * 1024 * 1024),
                            )
                            ->when(
                                $data['size_to'],
                                fn (Builder $query, $size): Builder => $query->where('size', '<=', $size * 1024 * 1024),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['campaign_id'] = $this->ownerRecord->id;
                        
                        // Auto-generate metadata based on file
                        if (isset($data['path'])) {
                            $file = Storage::disk('public')->get($data['path']);
                            if ($file) {
                                $data['size'] = strlen($file);
                                $data['mime_type'] = Storage::disk('public')->mimeType($data['path']);
                                
                                // Generate filename if not provided
                                if (empty($data['filename'])) {
                                    $data['filename'] = basename($data['path']);
                                }
                            }
                        }
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($record) {
                        return Storage::disk('public')->download($record->path, $record->original_name);
                    }),
                Tables\Actions\Action::make('feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured]))
                    ->label(fn ($record): string => $record->is_featured ? 'Unfeature' : 'Feature'),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        // Delete the actual file when deleting the record
                        if ($record->path && Storage::disk('public')->exists($record->path)) {
                            Storage::disk('public')->delete($record->path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            // Delete actual files when bulk deleting
                            $records->each(function ($record) {
                                if ($record->path && Storage::disk('public')->exists($record->path)) {
                                    Storage::disk('public')->delete($record->path);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('feature_selected')
                        ->label('Feature Selected')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => true]));
                        }),
                    Tables\Actions\BulkAction::make('unfeature_selected')
                        ->label('Unfeature Selected')
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => false]));
                        }),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}