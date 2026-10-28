<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sale Information')
                    ->schema([
                        Forms\Components\TextInput::make('sale_number')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabled(),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->name} ({$record->phone})")
                            ->label('Customer'),
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'display_name')
                            ->searchable()
                            ->preload()
                            ->label('Location'),
                        Forms\Components\DatePicker::make('sale_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateTotals($get, $set)),
                        Forms\Components\TextInput::make('discount_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateTotals($get, $set)),
                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateTotals($get, $set)),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(5),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                                'refunded' => 'Refunded',
                            ])
                            ->default('unpaid')
                            ->required(),
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateTotals($get, $set)),
                        Forms\Components\TextInput::make('balance_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'mobile_banking' => 'Mobile Banking',
                                'bank_transfer' => 'Bank Transfer',
                                'cheque' => 'Cheque',
                                'credit' => 'Credit / Due',
                            ])
                            ->searchable(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(100),
                    ])->columns(3),

                Forms\Components\Section::make('Notes & Terms')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(2),
                        Forms\Components\Textarea::make('terms_conditions')
                            ->maxLength(2000)
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    protected static function recalculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $subtotal = $get('subtotal') ?? 0;
        $discountPercentage = $get('discount_percentage') ?? 0;
        $discountAmount = $get('discount_amount') ?? 0;
        $taxAmount = $get('tax_amount') ?? 0;

        // If discount percentage is provided and discount amount is 0, calculate discount amount
        if ($discountPercentage > 0 && $discountAmount === 0) {
            $discountAmount = round($subtotal * $discountPercentage / 100, 2);
            $set('discount_amount', $discountAmount);
        }

        $total = $subtotal - $discountAmount + $taxAmount;
        $set('total_amount', round($total, 2));

        // Update balance
        $paidAmount = $get('paid_amount') ?? 0;
        $set('balance_amount', round($total - $paidAmount, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Walk-in'),
                Tables\Columns\TextColumn::make('sale_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => ['cancelled', 'refunded'],
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'purple' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance_amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->color(fn (float $state): string => $state > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Customer'),
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('sale_date', now()->toDateString())),
                Tables\Filters\Filter::make('has_balance')
                    ->label('Has Outstanding Balance')
                    ->query(fn (Builder $query): Builder => $query->where('balance_amount', '>', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Complete Sale')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Sale $record): bool => $record->status === 'pending' || $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(fn (Sale $record) => $record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ])),
                Tables\Actions\Action::make('add_payment')
                    ->label('Add Payment')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->visible(fn (Sale $record): bool => $record->balance_amount > 0 && $record->status === 'completed')
                    ->url(fn (Sale $record): string => route('filament.admin.resources.sale-payments.create', ['sale' => $record->id])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
