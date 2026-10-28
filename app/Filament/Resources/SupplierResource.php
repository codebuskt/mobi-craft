<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Purchasing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabled(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_person')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'blocked' => 'Blocked',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'distributor' => 'Distributor',
                                'manufacturer' => 'Manufacturer',
                                'wholesaler' => 'Wholesaler',
                                'importer' => 'Importer',
                                'local_vendor' => 'Local Vendor',
                                'service_provider' => 'Service Provider',
                            ])
                            ->default('local_vendor')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('mobile')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->rows(2),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('country')
                            ->maxLength(100)
                            ->default('Bangladesh'),
                    ])->columns(3),

                Forms\Components\Section::make('Tax & Legal')
                    ->schema([
                        Forms\Components\TextInput::make('trade_license_number')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('vat_registration_number')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('tax_identification_number')
                            ->maxLength(100),
                    ])->columns(3),

                Forms\Components\Section::make('Bank Details')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('bank_branch')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_routing_number')
                            ->maxLength(100),
                    ])->columns(2),

                Forms\Components\Section::make('Commercial Terms')
                    ->schema([
                        Forms\Components\TextInput::make('credit_limit')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0),
                        Forms\Components\TextInput::make('payment_terms_days')
                            ->numeric()
                            ->default(30)
                            ->suffix('days'),
                        Forms\Components\TextInput::make('lead_time_days')
                            ->numeric()
                            ->default(7)
                            ->suffix('days'),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->default(0),
                    ])->columns(4),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\KeyValue::make('preferences')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Preference'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                        'danger' => 'blocked',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info' => 'distributor',
                        'primary' => 'manufacturer',
                        'success' => 'wholesaler',
                        'warning' => 'importer',
                        'gray' => 'local_vendor',
                        'purple' => 'service_provider',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'distributor' => 'Distributor',
                        'manufacturer' => 'Manufacturer',
                        'wholesaler' => 'Wholesaler',
                        'importer' => 'Importer',
                        'local_vendor' => 'Local Vendor',
                        'service_provider' => 'Service Provider',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('outstanding_balance')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->color(fn (float $state): string => $state > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('available_credit')
                    ->money('BDT', locale: 'bn')
                    ->label('Available Credit')
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Orders')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_purchase_value')
                    ->label('Total Purchases')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('on_time_delivery_rate')
                    ->label('OTD Rate')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_order_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'blocked' => 'Blocked',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'distributor' => 'Distributor',
                        'manufacturer' => 'Manufacturer',
                        'wholesaler' => 'Wholesaler',
                        'importer' => 'Importer',
                        'local_vendor' => 'Local Vendor',
                        'service_provider' => 'Service Provider',
                    ]),
                Tables\Filters\Filter::make('has_credit')
                    ->label('Has Credit Limit')
                    ->query(fn (Builder $query): Builder => $query->where('credit_limit', '>', 0)),
                Tables\Filters\Filter::make('has_outstanding')
                    ->label('Has Outstanding Balance')
                    ->query(fn (Builder $query): Builder => $query->where('outstanding_balance', '>', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_orders')
                    ->label('Orders')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Supplier $record): string => route('filament.admin.resources.purchase-orders.index', ['tableFilters[supplier_id][value]' => $record->id])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'active']))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'inactive']))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PurchaseOrdersRelationManager::class,
            RelationManagers\PurchaseReceiptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
