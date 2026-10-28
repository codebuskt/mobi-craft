<?php

namespace App\Filament\Resources\RepairTicketResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Status History';

    protected static ?string $modelLabel = 'Status Change';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),

                BadgeColumn::make('from_status')
                    ->label('From')
                    ->colors([
                        'gray' => 'received',
                        'info' => 'diagnosing',
                        'warning' => 'waiting_approval',
                        'orange' => 'waiting_parts',
                        'primary' => 'in_repair',
                        'purple' => 'quality_check',
                        'success' => 'ready_delivery',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                BadgeColumn::make('to_status')
                    ->label('To')
                    ->colors([
                        'gray' => 'received',
                        'info' => 'diagnosing',
                        'warning' => 'waiting_approval',
                        'orange' => 'waiting_parts',
                        'primary' => 'in_repair',
                        'purple' => 'quality_check',
                        'success' => 'ready_delivery',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('changer.name')
                    ->label('Changed By')
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->wrap(),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
