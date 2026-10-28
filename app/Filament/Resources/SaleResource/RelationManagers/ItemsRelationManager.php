<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use App\Models\Product;
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
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn ($operation) => $operation === 'edit')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sku} - {$record->name} (Stock: {$record->stock_quantity}) (Price: {$record->sale_price})")
                    ->reactive()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('unit_price', $product->sale_price);
                                $set('tax_rate', $product->tax_rate);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->step(0.0001)
                    ->required()
                    ->default(1)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateLineTotal($get, $set)),
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
                Forms\Components\Textarea::make('notes')
                    ->maxLength(500)
                    ->rows(2),
            ])->columns(4);
    }

    protected static function recalculateLineTotal(Forms\Get $get, Forms\Set $set): void
    {
        $quantity = $get('quantity') ?? 0;
        $unitPrice = $get('unit_price') ?? 0;
        $discountPercentage = $get('discount_percentage') ?? 0;
        $discountAmount = $get('discount_amount') ?? 0;
        $taxRate = $get('tax_rate') ?? 0;
        $taxAmount = $get('tax_amount') ?? 0;

        $subtotal = $quantity * $unitPrice;

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
                    ->label('Qty'),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->label('Unit Price'),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->suffix('%')
                    ->sortable()
                    ->label('Disc %'),
                Tables\Columns\TextColumn::make('tax_rate')
                    ->suffix('%')
                    ->sortable()
                    ->label('Tax %'),
                Tables\Columns\TextColumn::make('line_total')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->label('Line Total'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sale_id'] = $this->getOwnerRecord()->id;

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
