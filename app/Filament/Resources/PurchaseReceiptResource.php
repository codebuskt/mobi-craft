<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReceiptResource\Pages;
use App\Filament\Resources\PurchaseReceiptResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseReceiptResource extends Resource
{
    protected static ?string $model = PurchaseReceipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Purchasing';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Receipt Information')
                    ->schema([
                        Forms\Components\TextInput::make('receipt_number')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabled(),
                        Forms\Components\Select::make('purchase_order_id')
                            ->relationship('purchaseOrder', 'po_number')
                            ->searchable()
                            ->preload()
                            ->label('Purchase Order')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->po_number} - {$record->supplier->name}")
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                if ($state) {
                                    $po = PurchaseOrder::find($state);
                                    if ($po) {
                                        $set('supplier_id', $po->supplier_id);
                                        $set('location_id', $po->location_id);
                                    }
                                }
                            })
                            ->disabled(fn ($operation) => $operation === 'edit'),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'display_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'display_name')
                            ->searchable()
                            ->preload()
                            ->label('Receive Location'),
                        Forms\Components\DatePicker::make('receipt_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_approval' => 'Pending Approval',
                                'approved' => 'Approved',
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
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set, $state) => self::recalculateTotals($get, $set)),
                        Forms\Components\TextInput::make('balance_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Forms\Components\Section::make('Quality & Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes')
                            ->maxLength(1000)
                            ->rows(2),
                        Forms\Components\Textarea::make('quality_notes')
                            ->label('Quality Inspection Notes')
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Record any quality issues, damaged items, or inspection results'),
                    ])->columns(2),

                Forms\Components\Section::make('Attachments')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('purchase-receipts')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->reorderable()
                            ->helperText('Delivery challan, GRN, quality reports, etc.'),
                    ]),
            ]);
    }

    protected static function recalculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $subtotal = $get('subtotal') ?? 0;
        $discountAmount = $get('discount_amount') ?? 0;
        $taxAmount = $get('tax_amount') ?? 0;

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
                Tables\Columns\TextColumn::make('receipt_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->url(fn (PurchaseReceipt $record): string => $record->purchaseOrder ? route('filament.admin.resources.purchase-orders.view', $record->purchaseOrder) : '#'),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('receipt_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending_approval',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
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
                Tables\Columns\TextColumn::make('balance_amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->color(fn (float $state): string => $state > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('received_by')
                    ->label('Received By')
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
                Tables\Filters\SelectFilter::make('purchase_order_id')
                    ->relationship('purchaseOrder', 'po_number')
                    ->searchable()
                    ->preload()
                    ->label('Purchase Order'),
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
                    ->visible(fn (PurchaseReceipt $record): bool => $record->canBeApproved())
                    ->requiresConfirmation()
                    ->action(fn (PurchaseReceipt $record) => $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseReceipts::route('/'),
            'create' => Pages\CreatePurchaseReceipt::route('/create'),
            'view' => Pages\ViewPurchaseReceipt::route('/{record}'),
            'edit' => Pages\EditPurchaseReceipt::route('/{record}/edit'),
        ];
    }
}
