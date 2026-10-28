<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'payment_method';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('payment_method')
            ->columns([
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'mobile_banking' => 'Mobile Banking',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        'credit' => 'Credit / Due',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->colors([
                        'success' => 'cash',
                        'primary' => 'card',
                        'info' => 'mobile_banking',
                        'warning' => 'bank_transfer',
                        'gray' => 'cheque',
                        'purple' => 'credit',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Received By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sale_id'] = $this->getOwnerRecord()->id;
                        $data['customer_id'] = $this->getOwnerRecord()->customer_id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
