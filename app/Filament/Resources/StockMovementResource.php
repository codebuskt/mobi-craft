<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Products & Inventory';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Movement Details')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->label('Product')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select product'),
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
                            ->label('Location')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select location'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'purchase' => 'Purchase Receipt',
                                'purchase_return' => 'Purchase Return',
                                'sale' => 'Sale',
                                'sale_return' => 'Sale Return',
                                'adjustment_in' => 'Adjustment (+)',
                                'adjustment_out' => 'Adjustment (-)',
                                'transfer_in' => 'Transfer In',
                                'transfer_out' => 'Transfer Out',
                                'repair_consumption' => 'Repair Consumption',
                                'repair_return' => 'Repair Return',
                                'damage' => 'Damage/Loss',
                                'loss' => 'Loss/Theft',
                                'opening_stock' => 'Opening Stock',
                                'stock_take' => 'Stock Take',
                            ])
                            ->required()
                            ->label('Movement Type')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                // Auto-set quantity sign based on type
                                if (in_array($state, ['purchase', 'adjustment_in', 'transfer_in', 'repair_return', 'opening_stock'])) {
                                    $set('quantity', abs($set('quantity') ?? 0));
                                } elseif (in_array($state, ['sale', 'adjustment_out', 'transfer_out', 'repair_consumption', 'damage', 'loss'])) {
                                    $set('quantity', -abs($set('quantity') ?? 0));
                                }
                            }),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->label('Quantity')
                            ->placeholder('Positive for inbound, negative for outbound')
                            ->helperText('Use positive for stock in, negative for stock out'),
                        Forms\Components\TextInput::make('unit_cost')
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0)
                            ->default(0)
                            ->label('Unit Cost')
                            ->prefix('৳')
                            ->helperText('Cost per unit at time of movement'),
                        Forms\Components\Placeholder::make('total_cost')
                            ->label('Total Cost')
                            ->content(fn (StockMovement $record) => $record->exists
                                ? '৳'.number_format($record->total_cost, 2)
                                : '৳0.00 (calculated automatically)'),
                    ])->columns(2),

                Forms\Components\Section::make('Transfer Locations (for transfers only)')
                    ->schema([
                        Forms\Components\Select::make('from_location_id')
                            ->relationship('fromLocation', 'name')
                            ->label('From Location')
                            ->searchable()
                            ->preload()
                            ->placeholder('Source location')
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['transfer_out', 'transfer_in'])),
                        Forms\Components\Select::make('to_location_id')
                            ->relationship('toLocation', 'name')
                            ->label('To Location')
                            ->searchable()
                            ->preload()
                            ->placeholder('Destination location')
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['transfer_out', 'transfer_in'])),
                    ])->columns(2)
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['transfer_out', 'transfer_in'])),

                Forms\Components\Section::make('Reference')
                    ->schema([
                        Forms\Components\TextInput::make('reference_type')
                            ->label('Reference Type')
                            ->placeholder('e.g., App\\Models\\Sale')
                            ->helperText('Model class name of the reference document'),
                        Forms\Components\TextInput::make('reference_id')
                            ->numeric()
                            ->label('Reference ID')
                            ->placeholder('ID of the reference document'),
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->placeholder('e.g., INV-2024-000001, REP-2024-000001')
                            ->helperText('Human-readable reference number'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->label('Notes')
                            ->placeholder('Reason for movement, special instructions, etc.'),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Metadata')
                            ->placeholder('batch: BATCH001, expiry: 2025-12-31, serial_numbers: [...]')
                            ->helperText('Flexible data: batch numbers, expiry dates, serial numbers, etc.'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('approved')
                            ->required()
                            ->label('Status'),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->label('Approved By')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select approver'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'success' => 'purchase',
                        'info' => 'purchase_return',
                        'primary' => 'sale',
                        'warning' => 'sale_return',
                        'success' => 'adjustment_in',
                        'danger' => 'adjustment_out',
                        'info' => 'transfer_in',
                        'warning' => 'transfer_out',
                        'warning' => 'repair_consumption',
                        'success' => 'repair_return',
                        'danger' => 'damage',
                        'danger' => 'loss',
                        'gray' => 'opening_stock',
                        'gray' => 'stock_take',
                    ])
                    ->formatStateUsing(fn (string $state): string => (new StockMovement(['type' => $state]))->type_label),
                Tables\Columns\TextColumn::make('formatted_quantity')
                    ->label('Qty')
                    ->sortable()
                    ->color(fn (StockMovement $record): string => $record->quantity > 0 ? 'success' : 'danger')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Unit Cost')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('running_balance')
                    ->label('Balance')
                    ->numeric(2)
                    ->sortable()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('from_location.name')
                    ->label('From')
                    ->badge()
                    ->color('warning')
                    ->toggleable()
                    ->visible(fn (StockMovement $record): bool => in_array($record->type, ['transfer_out', 'transfer_in'])),
                Tables\Columns\TextColumn::make('to_location.name')
                    ->label('To')
                    ->badge()
                    ->color('success')
                    ->toggleable()
                    ->visible(fn (StockMovement $record): bool => in_array($record->type, ['transfer_out', 'transfer_in'])),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Performed By')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'purchase' => 'Purchase Receipt',
                        'purchase_return' => 'Purchase Return',
                        'sale' => 'Sale',
                        'sale_return' => 'Sale Return',
                        'adjustment_in' => 'Adjustment (+)',
                        'adjustment_out' => 'Adjustment (-)',
                        'transfer_in' => 'Transfer In',
                        'transfer_out' => 'Transfer Out',
                        'repair_consumption' => 'Repair Consumption',
                        'repair_return' => 'Repair Return',
                        'damage' => 'Damage/Loss',
                        'loss' => 'Loss/Theft',
                        'opening_stock' => 'Opening Stock',
                        'stock_take' => 'Stock Take',
                    ])
                    ->label('Movement Type')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Status')
                    ->native(false),
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Tables\Filters\SelectFilter::make('location_id')
                    ->relationship('location', 'name')
                    ->label('Location')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (StockMovement $record): bool => $record->status === 'pending')
                        ->action(fn (StockMovement $record) => $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]))
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (StockMovement $record): bool => $record->status === 'pending')
                        ->action(fn (StockMovement $record) => $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records
                            ->where('status', 'pending')
                            ->each->update([
                                'status' => 'approved',
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->persistColumnSearchesInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'edit' => Pages\EditStockMovement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant', 'product', 'location', 'fromLocation', 'toLocation', 'user', 'approver'])
            ->withoutGlobalScopes(); // Admin can see all movements
    }
}
