<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Purchasing';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('po_number')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabled(),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'display_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($operation) => $operation === 'edit'),
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'display_name')
                            ->searchable()
                            ->preload()
                            ->label('Delivery Location'),
                        Forms\Components\DatePicker::make('order_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('expected_delivery_date')
                            ->label('Expected Delivery'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_approval' => 'Pending Approval',
                                'approved' => 'Approved',
                                'ordered' => 'Ordered',
                                'partial_received' => 'Partially Received',
                                'received' => 'Fully Received',
                                'cancelled' => 'Cancelled',
                                'rejected' => 'Rejected',
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
                        Forms\Components\TextInput::make('shipping_cost')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateTotals($get, $set)),
                        Forms\Components\TextInput::make('other_charges')
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
                    ])->columns(4),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                            ])
                            ->default('unpaid')
                            ->required(),
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0),
                        Forms\Components\TextInput::make('balance_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'card' => 'Card',
                                'mobile_banking' => 'Mobile Banking',
                                'cheque' => 'Cheque',
                            ])
                            ->searchable(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(100),
                    ])->columns(3),

                Forms\Components\Section::make('Notes & Terms')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes')
                            ->maxLength(1000)
                            ->rows(2),
                        Forms\Components\Textarea::make('supplier_notes')
                            ->label('Notes for Supplier')
                            ->maxLength(1000)
                            ->rows(2),
                        Forms\Components\Textarea::make('terms_conditions')
                            ->maxLength(2000)
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Attachments')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('purchase-orders')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->reorderable(),
                    ]),
            ]);
    }

    protected static function recalculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $subtotal = $get('subtotal') ?? 0;
        $discountPercentage = $get('discount_percentage') ?? 0;
        $discountAmount = $get('discount_amount') ?? 0;
        $taxAmount = $get('tax_amount') ?? 0;
        $shippingCost = $get('shipping_cost') ?? 0;
        $otherCharges = $get('other_charges') ?? 0;

        // If discount percentage is provided, calculate discount amount
        if ($discountPercentage > 0 && $discountAmount === 0) {
            $discountAmount = round($subtotal * $discountPercentage / 100, 2);
            $set('discount_amount', $discountAmount);
        }

        $total = $subtotal - $discountAmount + $taxAmount + $shippingCost + $otherCharges;
        $set('total_amount', round($total, 2));

        // Update balance
        $paidAmount = $get('paid_amount') ?? 0;
        $set('balance_amount', round($total - $paidAmount, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->label('Expected')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state && $state < now()->toDateString() ? 'danger' : ''),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending_approval',
                        'info' => 'approved',
                        'primary' => 'ordered',
                        'warning' => 'partial_received',
                        'success' => 'received',
                        'danger' => ['cancelled', 'rejected'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_approval' => 'Pending Approval',
                        'partial_received' => 'Partial Received',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
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
                Tables\Columns\TextColumn::make('created_by')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'ordered' => 'Ordered',
                        'partial_received' => 'Partially Received',
                        'received' => 'Fully Received',
                        'cancelled' => 'Cancelled',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Supplier'),
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Delivery')
                    ->query(fn (Builder $query): Builder => $query->where('expected_delivery_date', '<', now()->toDateString())
                        ->whereNotIn('status', ['received', 'cancelled', 'rejected'])),
                Tables\Filters\Filter::make('has_balance')
                    ->label('Has Outstanding Balance')
                    ->query(fn (Builder $query): Builder => $query->where('balance_amount', '>', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PurchaseOrder $record): bool => $record->canBeApproved())
                    ->requiresConfirmation()
                    ->action(fn (PurchaseOrder $record) => $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),
                Tables\Actions\Action::make('mark_ordered')
                    ->label('Mark as Ordered')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (PurchaseOrder $record): bool => $record->canBeOrdered())
                    ->requiresConfirmation()
                    ->action(fn (PurchaseOrder $record) => $record->update([
                        'status' => 'ordered',
                        'ordered_at' => now(),
                    ])),
                Tables\Actions\Action::make('receive')
                    ->label('Receive Items')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (PurchaseOrder $record): bool => $record->canBeReceived())
                    ->url(fn (PurchaseOrder $record): string => route('filament.admin.resources.purchase-receipts.create', ['purchase_order' => $record->id])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records
                            ->filter(fn ($r) => $r->canBeApproved())
                            ->each->update([
                                'status' => 'approved',
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\ReceiptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
