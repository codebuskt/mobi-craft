<?php

namespace App\Filament\Resources\PurchaseReceiptResource\RelationManagers;

use App\Models\PurchaseOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'product.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('purchase_order_item_id')
                    ->relationship('purchaseOrderItem', 'product.name')
                    ->searchable()
                    ->preload()
                    ->label('PO Item')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $product = $record->product;
                        $pending = $record->pending_quantity;

                        return "{$product->sku} - {$product->name} (Pending: {$pending})";
                    })
                    ->reactive()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                        if ($state) {
                            $poItem = PurchaseOrderItem::find($state);
                            if ($poItem) {
                                $set('product_id', $poItem->product_id);
                                $set('quantity', $poItem->pending_quantity);
                                $set('unit_price', $poItem->unit_price);
                                $set('discount_percentage', $poItem->discount_percentage);
                                $set('discount_amount', $poItem->discount_amount);
                                $set('tax_rate', $poItem->tax_rate);
                                $set('tax_amount', $poItem->tax_amount);
                            }
                        }
                    })
                    ->disabled(fn ($operation) => $operation === 'edit'),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sku} - {$record->name}")
                    ->disabled(fn ($operation) => $operation !== 'create' || $get('purchase_order_item_id')),
                Forms\Components\TextInput::make('quantity')
                    ->label('Ordered Quantity')
                    ->numeric()
                    ->step(0.0001)
                    ->required()
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('accepted_quantity')
                    ->label('Accepted Quantity')
                    ->numeric()
                    ->step(0.0001)
                    ->required()
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
                Forms\Components\TextInput::make('rejected_quantity')
                    ->numeric()
                    ->step(0.0001)
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
                Forms\Components\Textarea::make('rejection_reason')
                    ->maxLength(500)
                    ->rows(2)
                    ->visible(fn (Forms\Get $get): bool => ($get('rejected_quantity') ?? 0) > 0),
                Forms\Components\TextInput::make('unit_price')
                    ->numeric()
                    ->step(0.0001)
                    ->required()
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
                Forms\Components\TextInput::make('discount_percentage')
                    ->numeric()
                    ->step(0.01)
                    ->suffix('%')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
                Forms\Components\TextInput::make('discount_amount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('BDT')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
                Forms\Components\TextInput::make('tax_rate')
                    ->numeric()
                    ->step(0.01)
                    ->suffix('%')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
                Forms\Components\TextInput::make('tax_amount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('BDT')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
                Forms\Components\TextInput::make('line_total')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('BDT')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->hidden(),
                Forms\Components\KeyValue::make('metadata')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->addActionLabel('Add Metadata')
                    ->helperText('Batch numbers, serial numbers, etc.'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(500)
                    ->rows(2),
            ])->columns(4);
    }

    protected static function recalculateLineTotal(Forms\Get $get, Forms\Set $set): void
    {
        $acceptedQuantity = $get('accepted_quantity') ?? 0;
        $unitPrice = $get('unit_price') ?? 0;
        $discountPercentage = $get('discount_percentage') ?? 0;
        $discountAmount = $get('discount_amount') ?? 0;
        $taxRate = $get('tax_rate') ?? 0;
        $taxAmount = $get('tax_amount') ?? 0;

        $subtotal = $acceptedQuantity * $unitPrice;

        // If discount percentage is provided and discount amount is 0, calculate discount amount
        if ($discountPercentage > 0 && $discountAmount === 0) {
            $discountAmount = round($subtotal * $discountPercentage / 100, 2);
            $set('discount_amount', $discountAmount);
        }

        $taxable = $subtotal - $discountAmount;

        // If tax rate is provided and tax amount is 0, calculate tax amount
        if ($taxRate > 0 && $taxAmount === 0) {
            $taxAmount = round($taxable * $taxRate / 100, 2);
            $set('tax_amount', $taxAmount);
        }

        $total = $taxable + $taxAmount;
        $set('line_total', round($total, 2));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric(4)
                    ->sortable()
                    ->label('Ordered Qty'),
                Tables\Columns\TextColumn::make('accepted_quantity')
                    ->numeric(4)
                    ->sortable()
                    ->label('Accepted')
                    ->color('success'),
                Tables\Columns\TextColumn::make('rejected_quantity')
                    ->numeric(4)
                    ->sortable()
                    ->label('Rejected')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->label('Unit Cost'),
                Tables\Columns\TextColumn::make('line_total')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->label('Line Total'),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['purchase_receipt_id'] = $this->getOwnerRecord()->id;

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
            ])
            ->defaultSort('sort_order');
    }
}
