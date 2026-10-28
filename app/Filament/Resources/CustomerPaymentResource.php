<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerPaymentResource\Pages;
use App\Models\Customer;
use App\Models\CustomerPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerPaymentResource extends Resource
{
    protected static ?string $model = CustomerPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-bangladeshi';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->name} (Due: {$record->outstanding_balance})")
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    if ($customer) {
                                        $set('amount', $customer->outstanding_balance);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('sale_id')
                            ->relationship('sale', 'sale_number')
                            ->searchable()
                            ->preload()
                            ->label('Allocate to Sale')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sale_number} (Balance: {$record->balance_amount})")
                            ->disabled(fn (Forms\Get $get): bool => ! $get('customer_id')),
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->url(fn (CustomerPayment $record): string => $record->customer ? route('filament.admin.resources.customers.view', $record->customer) : '#'),
                Tables\Columns\TextColumn::make('sale.sale_number')
                    ->label('Allocated Sale')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->url(fn (CustomerPayment $record): string => $record->sale ? route('filament.admin.resources.sales.view', $record->sale) : '#'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'mobile_banking' => 'Mobile Banking',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->colors([
                        'success' => 'cash',
                        'primary' => 'card',
                        'info' => 'mobile_banking',
                        'warning' => 'bank_transfer',
                        'gray' => 'cheque',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('allocated_amount')
                    ->money('BDT', locale: 'bn')
                    ->label('Allocated')
                    ->sortable()
                    ->toggleable(),
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
                    ]),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Customer'),
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
            'index' => Pages\ListCustomerPayments::route('/'),
            'create' => Pages\CreateCustomerPayment::route('/create'),
            'view' => Pages\ViewCustomerPayment::route('/{record}'),
            'edit' => Pages\EditCustomerPayment::route('/{record}/edit'),
        ];
    }
}
