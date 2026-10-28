<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalePaymentResource\Pages;
use App\Models\Sale;
use App\Models\SalePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalePaymentResource extends Resource
{
    protected static ?string $model = SalePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('sale_id')
                            ->relationship('sale', 'sale_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sale_number} - ".($record->customer?->name ?? 'Walk-in')." (Balance: {$record->balance_amount})")
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                if ($state) {
                                    $sale = Sale::find($state);
                                    if ($sale) {
                                        $set('customer_id', $sale->customer_id);
                                        $set('amount', $sale->balance_amount);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'mobile_banking' => 'Mobile Banking',
                                'bank_transfer' => 'Bank Transfer',
                                'cheque' => 'Cheque',
                                'credit' => 'Credit / Due',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(100),
                        Forms\Components\DatePicker::make('payment_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->rows(2),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.sale_number')
                    ->label('Sale')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->url(fn (SalePayment $record): string => $record->sale ? route('filament.admin.resources.sales.view', $record->sale) : '#'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Walk-in'),
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'mobile_banking' => 'Mobile Banking',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        'credit' => 'Credit / Due',
                    ]),
                Tables\Filters\SelectFilter::make('sale_id')
                    ->relationship('sale', 'sale_number')
                    ->searchable()
                    ->preload()
                    ->label('Sale'),
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('payment_date', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalePayments::route('/'),
            'create' => Pages\CreateSalePayment::route('/create'),
            'view' => Pages\ViewSalePayment::route('/{record}'),
            'edit' => Pages\EditSalePayment::route('/{record}/edit'),
        ];
    }
}
