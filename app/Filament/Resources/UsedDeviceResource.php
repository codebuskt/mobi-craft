<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsedDeviceResource\Pages;
use App\Filament\Resources\UsedDeviceResource\RelationManagers;
use App\Models\UsedDevice;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class UsedDeviceResource extends Resource
{
    protected static ?string $model = UsedDevice::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Products & Inventory';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Card::make('Basic Information')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Category')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select category'),
                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->label('Brand')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select brand'),
                        TextInput::make('device_number')
                            ->label('Device Number')
                            ->disabled(! $form instanceof Filament\Forms\Concerns\FilamentForm || true)
                            ->placeholder('Auto-generated: UD-2026-00001'),
                        TextInput::make('brand')
                            ->required()
                            ->maxLength(100)
                            ->label('Brand'),
                        TextInput::make('model')
                            ->maxLength(100)
                            ->label('Model'),
                        TextInput::make('color')
                            ->maxLength(50)
                            ->label('Color'),
                        TextInput::make('storage')
                            ->maxLength(50)
                            ->label('Storage'),
                        Select::make('carrier')
                            ->options([
                                'unlocked' => 'Unlocked',
                                'verizon' => 'Verizon',
                                'att' => 'AT&T',
                                'tmobile' => 'T-Mobile',
                                'other' => 'Other',
                            ])
                            ->label('Carrier'),
                        Select::make('purchase_source')
                            ->options([
                                'customer' => 'Customer',
                                'supplier' => 'Supplier',
                                'trade_in' => 'Trade-in',
                                'auction' => 'Auction',
                                'other' => 'Other',
                            ])
                            ->default('customer')
                            ->label('Purchase Source'),
                        TextInput::make('purchase_source_reference')
                            ->maxLength(255)
                            ->label('Source Reference / Customer Name'),
                        DatePicker::make('purchase_date')
                            ->label('Purchase Date'),
                        TextInput::make('acquisition_cost')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Acquisition Cost')
                            ->prefix('৳')
                            ->helperText('What was paid for the device'),
                        TextInput::make('additional_costs')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Additional Costs')
                            ->prefix('৳')
                            ->helperText('Shipping, fees, import costs, etc.'),
                    ])->columns(2),

                Card::make('Identification')
                    ->schema([
                        TextInput::make('imei')
                            ->label('IMEI')
                            ->maxLength(20)
                            ->helperText('Enter 15-digit IMEI number. Will be encrypted and stored securely.')
                            ->visible(fn (bool $columnSpan = null): bool => true)
                            ->afterStateUpdated(fn (string $operation, $state, \Filament\Forms\Set $set) => $operation === 'create' || $operation === 'update' ? $set('imei_encrypted', Crypt::encryptString($state ?? '')) : null),
                        TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->maxLength(50)
                            ->helperText('Device serial number, if applicable')
                            ->afterStateUpdated(fn (string $operation, $state, \Filament\Forms\Set $set) => $operation === 'create' || $operation === 'update' ? $set('serial_encrypted', Crypt::encryptString($state ?? '')) : null),
                    ])->columns(2),

                Card::make('Condition & Inspection')
                    ->schema([
                        Select::make('condition_grade')
                            ->options([
                                'a_plus' => 'A+ (Excellent)',
                                'a' => 'A (Excellent)',
                                'b_plus' => 'B+ (Good)',
                                'b' => 'B (Good)',
                                'c_plus' => 'C+ (Fair)',
                                'c' => 'C (Fair)',
                                'd' => 'D (Poor)',
                            ])
                            ->default('b')
                            ->label('Condition Grade')
                            ->native(false),
                        Select::make('functional_status')
                            ->options([
                                'fully_functional' => 'Fully Functional',
                                'minor_issues' => 'Minor Issues',
                                'major_issues' => 'Major Issues',
                                'parts_only' => 'Parts Only',
                            ])
                            ->default('fully_functional')
                            ->label('Functional Status')
                            ->native(false),
                        Textarea::make('inspection_notes')
                            ->rows(3)
                            ->maxLength(500)
                            ->label('Inspection Notes')
                            ->placeholder('Notes from condition inspection'),
                        Select::make('inspection_checklist')
                            ->label('Inspection Checklist')
                            ->multiple()
                            ->options([
                                'screen' => 'Screen / Display',
                                'battery' => 'Battery Health',
                                'charging_port' => 'Charging Port',
                                'audio' => 'Audio Quality',
                                'cameras' => 'Cameras',
                                'buttons' => 'Physical Buttons',
                                'speakers' => 'Speakers',
                                'wifi' => 'WiFi Connectivity',
                                'bluetooth' => 'Bluetooth',
                                'network' => 'Network Signal',
                                'os' => 'Operating System',
                                'water_damage' => 'Water Damage Indicators',
                                'cosmetic' => 'Cosmetic Condition',
                                'other' => 'Other',
                            ]),
                    ])->columns(2),

                Card::make('Refurbishment')
                    ->schema([
                        TextInput::make('refurbishment_cost')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Refurbishment Cost')
                            ->prefix('৳')
                            ->helperText('Parts + labor cost'),
                        Textarea::make('refurbishment_notes')
                            ->rows(2)
                            ->maxLength(300)
                            ->label('Refurbishment Notes')
                            ->placeholder('Parts replaced, repairs done, etc.'),
                        DatePicker::make('refurbishment_completed_at')
                            ->label('Refurbishment Completed')
                            ->nullable(),
                    ])->columns(2),

                Card::make('Listing & Sale')
                    ->schema([
                        Select::make('listing_status')
                            ->options([
                                'acquired' => 'Acquired',
                                'inspecting' => 'Inspecting',
                                'refurbishing' => 'Refurbishing',
                                'ready_for_sale' => 'Ready for Sale',
                                'listed' => 'Listed',
                                'reserved' => 'Reserved',
                                'sold' => 'Sold',
                                'returned' => 'Returned',
                                'written_off' => 'Written Off',
                            ])
                            ->default('acquired')
                            ->label('Listing Status')
                            ->native(false),
                        TextInput::make('listing_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Listing Price')
                            ->prefix('৳')
                            ->hideWhen(fn (UsedDevice $record): bool => $record->listing_status !== 'listed' && $record->listing_status !== 'ready_for_sale')
                            ->helperText('Asking price'),
                        TextInput::make('minimum_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Minimum Price')
                            ->prefix('৳')
                            ->helperText('Floor price - will not sell below this'),
                        DatePicker::make('listed_at')
                            ->label('Listed Date')
                            ->nullable()
                            ->hideWhen(fn (UsedDevice $record): bool => $record->listing_status !== 'listed' && $record->listing_status !== 'ready_for_sale'),
                        Select::make('listing_platform')
                            ->options([
                                'in_store' => 'In-Store',
                                'online' => 'Online Store',
                                'facebook' => 'Facebook Marketplace',
                                'banglalist' => 'Banglalist',
                                'olx' => 'OLX',
                                'other' => 'Other',
                            ])
                            ->label('Listing Platform')
                            ->nullable(),
                        Textarea::make('listing_description')
                            ->rows(2)
                            ->maxLength(300)
                            ->label('Listing Description')
                            ->placeholder('Brief description for listing...')
                            ->hideWhen(fn (UsedDevice $record): bool => ! in_array($record->listing_status, ['listed', 'ready_for_sale'])),
                    ])->columns(2),

                Card::make('Sale Information')
                    ->schema([
                        TextInput::make('sale_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Sale Price')
                            ->prefix('৳')
                            ->helperText('Final sale price (auto-calculated margin if available)')
                            ->disabled(true)
                            ->helperText('Sale price is system-calculated'),
                        DatePicker::make('sold_at')
                            ->label('Sold Date')
                            ->disabled(true)
                            ->helperText('Date device was sold'),
                        TextInput::make('gross_margin')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Gross Margin')
                            ->prefix('৳')
                            ->helperText('Sale price - total cost'),
                        TextInput::make('margin_percentage')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(500)
                            ->default(0)
                            ->label('Margin Percentage')
                            ->suffix('%')
                            ->helperText('Gross margin %'),
                    ])->columns(2),

                Card::make('Documentation')
                    ->schema([
                        Toggle::make('ownership_verified')
                            ->default(false)
                            ->label('Ownership Verified'),
                        Textarea::make('ownership_notes')
                            ->rows(2)
                            ->maxLength(300)
                            ->label('Ownership Notes')
                            ->placeholder('Notes on ownership verification...'),
                        Textarea::make('notes')
                            ->rows(2)
                            ->maxLength(300)
                            ->label('Internal Notes')
                            ->placeholder('Internal notes about the device'),
                    ])->columns(2),

                Card::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('intake_photos')
                            ->label('Intake Photos')
                            ->multiple()
                            ->image()
                            ->directory('used_devices/intake')
                            ->maxSize(2048)
                            ->columnSpanFull()
                            ->helperText('Photos of device at acquisition'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('thumbnail')
                    ->label('Image')
                    ->circular()
                    ->size(40)
                    ->placeholder(fn (UsedDevice $record) => mb_substr($record->display_name, 0, 1)),

                TextColumn::make('display_name')
                    ->label('Device')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('device_number')
                    ->label('Device #')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('imei')
                    ->label('IMEI')
                    ->searchable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state): ?string => $state ? substr($state, -4, 4) . '****' : null)
                    ->tooltip(fn ($state): ?string => $state),

                TextColumn::make('serial_number')
                    ->label('Serial')
                    ->searchable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state): ?string => $state ? substr($state, -6) . '****' : null)
                    ->tooltip(fn ($state): ?string => $state),

                TextColumn::make('brand')
                    ->label('Brand')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('storage')
                    ->label('Storage')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('color')
                    ->label('Color')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('condition_grade')
                    ->label('Condition')
                    ->colors([
                        'a_plus' => 'success',
                        'a' => 'success',
                        'b_plus' => 'info',
                        'b' => 'info',
                        'c_plus' => 'warning',
                        'c' => 'warning',
                        'd' => 'danger',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'a_plus' => 'A+',
                        'a' => 'A',
                        'b_plus' => 'B+',
                        'b' => 'B',
                        'c_plus' => 'C+',
                        'c' => 'C',
                        'd' => 'D',
                        default => ucfirst($state),
                    }),

                BadgeColumn::make('functional_status')
                    ->label('Status')
                    ->colors([
                        'fully_functional' => 'success',
                        'minor_issues' => 'warning',
                        'major_issues' => 'orange',
                        'parts_only' => 'danger',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fully_functional' => 'Fully Functional',
                        'minor_issues' => 'Minor Issues',
                        'major_issues' => 'Major Issues',
                        'parts_only' => 'Parts Only',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),

                BadgeColumn::make('listing_status')
                    ->label('Listing Status')
                    ->colors([
                        'acquired' => 'gray',
                        'inspecting' => 'info',
                        'refurbishing' => 'warning',
                        'ready_for_sale' => 'success',
                        'listed' => 'primary',
                        'reserved' => 'orange',
                        'sold' => 'success',
                        'returned' => 'danger',
                        'written_off' => 'gray',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'acquired' => 'Acquired',
                        'inspecting' => 'Inspecting',
                        'refurbishing' => 'Refurbishing',
                        'ready_for_sale' => 'Ready for Sale',
                        'listed' => 'Listed',
                        'reserved' => 'Reserved',
                        'sold' => 'Sold',
                        'returned' => 'Returned',
                        'written_off' => 'Written Off',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),

                TextColumn::make('acquisition_cost')
                    ->label('Acq. Cost')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('refurbishment_cost')
                    ->label('Refurb Cost')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('listing_price')
                    ->label('Listing Price')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn (UsedDevice $record): bool => in_array($record->listing_status, ['listed', 'ready_for_sale'])),

                TextColumn::make('sale_price')
                    ->label('Sale Price')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn (UsedDevice $record): bool => $record->sold_at),

                TextColumn::make('gross_margin')
                    ->label('Margin')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn (UsedDevice $record): bool => $record->gross_margin),

                TextColumn::make('margin_percentage')
                    ->label('Margin %')
                    ->numeric(1)
                    ->sortable()
                    ->toggleable()
                    ->visible(fn (UsedDevice $record): bool => $record->margin_percentage),

                TextColumn::make('warranty_months')
                    ->label('Warranty')
                    ->numeric()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('listing_status')
                    ->options([
                        'acquired' => 'Acquired',
                        'inspecting' => 'Inspecting',
                        'refurbishing' => 'Refurbishing',
                        'ready_for_sale' => 'Ready for Sale',
                        'listed' => 'Listed',
                        'reserved' => 'Reserved',
                        'sold' => 'Sold',
                        'returned' => 'Returned',
                        'written_off' => 'Written Off',
                    ])
                    ->label('Listing Status')
                    ->native(false),

                SelectFilter::make('condition_grade')
                    ->options([
                        'a_plus' => 'A+',
                        'a' => 'A',
                        'b_plus' => 'B+',
                        'b' => 'B',
                        'c_plus' => 'C+',
                        'c' => 'C',
                        'd' => 'D',
                    ])
                    ->label('Condition Grade')
                    ->native(false),

                SelectFilter::make('functional_status')
                    ->options([
                        'fully_functional' => 'Fully Functional',
                        'minor_issues' => 'Minor Issues',
                        'major_issues' => 'Major Issues',
                        'parts_only' => 'Parts Only',
                    ])
                    ->label('Functional Status')
                    ->native(false),

                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('purchase_source')
                    ->options([
                        'customer' => 'Customer',
                        'supplier' => 'Supplier',
                        'trade_in' => 'Trade-in',
                        'auction' => 'Auction',
                        'other' => 'Other',
                    ])
                    ->label('Purchase Source')
                    ->native(false),

                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('ownership_verified')
                    ->options([
                        false => 'Not Verified',
                        true => 'Verified',
                    ])
                    ->label('Ownership Verified')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('listing_status')
                    ->label('Listing Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->native(false),

            ])
            ->actions([

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_as_sold')
                    ->label('Mark as Sold')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Mark Device as Sold')
                    ->modalDescription('This will change the status to Sold and record the sale price.')
                    ->form([
                        Select\Select::make('sale_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->label('Sale Price')
                            ->prefix('৳')
                            ->helperText('Enter the final sale price'),
                        Select\Select::make('sale_date')
                            ->date()
                            ->label('Sale Date')
                            ->default(now()),
                    ])
                    ->action(function (UsedDevice $record, array $data): void {
                        $record->markAsSold(
                            $data['sale_price'],
                            $data['sale_date'] ? \Carbon\Carbon::parse($data['sale_date'])->toDateString() : null
                        );
                    })
                    ->modalFooter(
                        Tables\Actions\Action::make('cancel')
                            ->label('Cancel')
                            ->button()
                    ),

                Tables\Actions\Action::make('print_label')
                    ->label('Print Label')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn (UsedDevice $record): string => $record->listing_status === 'sold' ? '#' : '#')
                    ->visible(fn (UsedDevice $record): bool => $record->listing_status === 'sold'),

                Tables\Actions\Action::make('refresh_margin')
                    ->label('Refresh Margin')
                    ->icon('heroicon-o-refresh')
                    ->color('primary')
                    ->action(function (UsedDevice $record): void {
                        $record->recalculateMargin();
                        $record->save();
                    }),

            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\BulkAction::make('set_refurbishing')
                        ->label('Set to Refurbishing')
                        ->icon('heroicon-o-arrow-counterclockwise')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->listing_status = 'refurbishing';
                                $record->save();
                            }
                        }),

                    Tables\Actions\BulkAction::make('set_sold')
                        ->label('Mark as Sold (Same Price)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->listing_status = 'sold';
                                $record->recalculateMargin();
                                $record->save();
                            }
                        }),

                    Tables\Actions\BulkAction::make('set_ready_for_sale')
                        ->label('Set Ready for Sale')
                        ->icon('heroicon-o-shopping-bag')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->listing_status = 'ready_for_sale';
                                $record->save();
                            }
                        }),

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
            // Relation managers removed until models are created
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsedDevices::route('/'),
            'create' => Pages\CreateUsedDevice::route('/create'),
            'edit' => Pages\EditUsedDevice::route('/{record}/edit'),
            'view' => Pages\ViewUsedDevice::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes(); // Admin can see all devices
    }
}