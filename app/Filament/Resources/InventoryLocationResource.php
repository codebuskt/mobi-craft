<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryLocationResource\Pages;
use App\Models\InventoryLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryLocationResource extends Resource
{
    protected static ?string $model = InventoryLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Products & Inventory';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Location Information')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->relationship('parent', 'name')
                            ->label('Parent Location')
                            ->searchable()
                            ->preload()
                            ->placeholder('Top-level location (no parent)')
                            ->helperText('Leave empty for top-level location'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Location Name'),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->label('Code')
                            ->placeholder('e.g., WH001, SF001, RB001')
                            ->helperText('Short unique code for this location')
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->label('Slug')
                            ->placeholder('e.g., warehouse, shop-floor, repair-bay')
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'warehouse' => 'Warehouse',
                                'shop_floor' => 'Shop Floor',
                                'repair_bay' => 'Repair Bay',
                                'showroom' => 'Showroom',
                                'storage' => 'Storage',
                                'transit' => 'In Transit',
                            ])
                            ->default('storage')
                            ->required()
                            ->label('Location Type'),
                        Forms\Components\Select::make('manager_id')
                            ->relationship('manager', 'name')
                            ->label('Manager')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select manager (optional)'),
                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->label('Internal Address')
                            ->placeholder('Location within premises (e.g., Building A, Floor 2, Row 3)'),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->label('Description')
                            ->placeholder('Additional details about this location...'),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active')
                            ->helperText('Inactive locations cannot receive new stock'),
                        Forms\Components\Toggle::make('is_default')
                            ->default(false)
                            ->label('Default Location')
                            ->helperText('Used as default for new stock entries (only one per tenant)'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order')
                            ->helperText('Lower values appear first'),
                        Forms\Components\KeyValue::make('settings')
                            ->label('Advanced Settings')
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Setting')
                            ->placeholder('temperature_controlled: true, security_level: high')
                            ->helperText('Custom settings for this location'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->formatStateUsing(fn (InventoryLocation $record): string => $record->path),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'warehouse',
                        'success' => 'shop_floor',
                        'warning' => 'repair_bay',
                        'info' => 'showroom',
                        'gray' => 'storage',
                        'danger' => 'transit',
                    ])
                    ->formatStateUsing(fn (string $state): string => $record->type_label),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Manager')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock_movements_count')
                    ->label('Movements')
                    ->counts('stockMovements')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'warehouse' => 'Warehouse',
                        'shop_floor' => 'Shop Floor',
                        'repair_bay' => 'Repair Bay',
                        'showroom' => 'Showroom',
                        'storage' => 'Storage',
                        'transit' => 'In Transit',
                    ])
                    ->label('Type')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
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
            'index' => Pages\ListInventoryLocations::route('/'),
            'create' => Pages\CreateInventoryLocation::route('/create'),
            'edit' => Pages\EditInventoryLocation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant', 'parent', 'manager', 'children'])
            ->withoutGlobalScopes(); // Admin can see all locations
    }
}
