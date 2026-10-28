<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopProfileResource\Pages;
use App\Models\ShopProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShopProfileResource extends Resource
{
    protected static ?string $model = ShopProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Tenant Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'business_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Information')
                    ->schema([
                        Forms\Components\TextInput::make('business_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Business Name'),
                        Forms\Components\TextInput::make('trade_license_number')
                            ->label('Trade License Number')
                            ->maxLength(100)
                            ->placeholder('Optional'),
                        Forms\Components\DatePicker::make('trade_license_expiry')
                            ->label('Trade License Expiry')
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('vat_registration_number')
                            ->label('VAT Registration Number')
                            ->maxLength(50)
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('tax_identification_number')
                            ->label('Tax Identification Number (TIN)')
                            ->maxLength(50)
                            ->placeholder('Optional'),
                    ])->columns(2),

                Forms\Components\Section::make('Address & Contact')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->rows(2)
                            ->label('Street Address'),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100)
                            ->placeholder('City'),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(100)
                            ->placeholder('State/Province'),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(20)
                            ->label('Postal Code')
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('country')
                            ->default('Bangladesh')
                            ->maxLength(100)
                            ->label('Country'),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->tel()
                            ->maxLength(20)
                            ->label('Phone')
                            ->placeholder('+8801XXXXXXXXX'),
                        Forms\Components\TextInput::make('alternate_phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Alternate Phone')
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->label('Email'),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->label('Website')
                            ->placeholder('https://example.com'),
                    ])->columns(2),

                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('shop-profiles/logos')
                            ->maxSize(1024)
                            ->label('Logo')
                            ->columnSpanFull()
                            ->helperText('Recommended: 300x100px, PNG/SVG'),
                        Forms\Components\FileUpload::make('favicon')
                            ->image()
                            ->directory('shop-profiles/favicons')
                            ->maxSize(256)
                            ->label('Favicon')
                            ->helperText('Recommended: 32x32px, PNG/ICO'),
                        Forms\Components\TextInput::make('invoice_prefix')
                            ->default('INV')
                            ->maxLength(10)
                            ->label('Invoice Prefix')
                            ->helperText('e.g., INV, SAL, POS'),
                        Forms\Components\TextInput::make('repair_ticket_prefix')
                            ->default('REP')
                            ->maxLength(10)
                            ->label('Repair Ticket Prefix'),
                        Forms\Components\TextInput::make('purchase_order_prefix')
                            ->default('PO')
                            ->maxLength(10)
                            ->label('Purchase Order Prefix'),
                        Forms\Components\TextInput::make('quotation_prefix')
                            ->default('QUO')
                            ->maxLength(10)
                            ->label('Quotation Prefix'),
                    ])->columns(2),

                Forms\Components\Section::make('Localization')
                    ->schema([
                        Forms\Components\Select::make('currency')
                            ->options([
                                'BDT' => 'Bangladeshi Taka (BDT)',
                                'USD' => 'US Dollar (USD)',
                                'EUR' => 'Euro (EUR)',
                                'GBP' => 'British Pound (GBP)',
                                'INR' => 'Indian Rupee (INR)',
                            ])
                            ->default('BDT')
                            ->required()
                            ->label('Currency'),
                        Forms\Components\TextInput::make('currency_symbol')
                            ->default('৳')
                            ->maxLength(5)
                            ->label('Currency Symbol')
                            ->helperText('Symbol used for display, e.g., ৳, $, €'),
                        Forms\Components\Select::make('timezone')
                            ->options([
                                'Asia/Dhaka' => 'Asia/Dhaka (UTC+6)',
                                'Asia/Kolkata' => 'Asia/Kolkata (UTC+5:30)',
                                'UTC' => 'UTC',
                            ])
                            ->default('Asia/Dhaka')
                            ->required()
                            ->label('Timezone')
                            ->searchable(),
                        Forms\Components\Select::make('locale')
                            ->options([
                                'en' => 'English',
                                'bn' => 'Bangla (বাংলা)',
                            ])
                            ->default('en')
                            ->required()
                            ->label('Language'),
                        Forms\Components\TextInput::make('date_format')
                            ->default('d/m/Y')
                            ->maxLength(20)
                            ->label('Date Format')
                            ->placeholder('e.g., d/m/Y, Y-m-d'),
                        Forms\Components\TextInput::make('time_format')
                            ->default('H:i')
                            ->maxLength(10)
                            ->label('Time Format')
                            ->placeholder('e.g., H:i, h:i A'),
                    ])->columns(2),

                Forms\Components\Section::make('Financial Settings')
                    ->schema([
                        Forms\Components\TextInput::make('default_tax_rate')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->label('Default Tax Rate (%)')
                            ->suffix('%'),
                        Forms\Components\TextInput::make('tax_label')
                            ->default('VAT')
                            ->maxLength(20)
                            ->label('Tax Label')
                            ->helperText('Label shown on invoices, e.g., VAT, GST, Tax'),
                        Forms\Components\Toggle::make('tax_inclusive')
                            ->default(false)
                            ->label('Tax Inclusive Pricing')
                            ->helperText('If enabled, prices entered include tax'),
                        Forms\Components\TextInput::make('payment_terms_days')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365)
                            ->default(0)
                            ->label('Default Payment Terms (Days)')
                            ->helperText('Days until payment is due'),
                        Forms\Components\TextInput::make('rounding_precision')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0.50)
                            ->label('Rounding Precision')
                            ->helperText('Round totals to nearest value (e.g., 0.50 = nearest 50 paisa)'),
                    ])->columns(2),

                Forms\Components\Section::make('POS Settings')
                    ->schema([
                        Forms\Components\Toggle::make('enable_barcode_scanner')
                            ->default(true)
                            ->label('Enable Barcode Scanner'),
                        Forms\Components\Toggle::make('enable_customer_display')
                            ->default(false)
                            ->label('Enable Customer Display'),
                        Forms\Components\Toggle::make('auto_print_receipt')
                            ->default(false)
                            ->label('Auto Print Receipt'),
                        Forms\Components\Select::make('receipt_printer_type')
                            ->options([
                                'thermal' => 'Thermal (80mm/58mm)',
                                'a4' => 'A4/Letter',
                                'dot-matrix' => 'Dot Matrix',
                            ])
                            ->default('thermal')
                            ->required()
                            ->label('Receipt Printer Type'),
                        Forms\Components\TextInput::make('receipt_width')
                            ->numeric()
                            ->minValue(40)
                            ->maxValue(210)
                            ->default(80)
                            ->label('Receipt Width (mm)')
                            ->helperText('For thermal printers: 58 or 80'),
                    ])->columns(2),

                Forms\Components\Section::make('Repair Settings')
                    ->schema([
                        Forms\Components\Toggle::make('enable_imei_tracking')
                            ->default(true)
                            ->label('Enable IMEI/Serial Tracking'),
                        Forms\Components\TextInput::make('default_warranty_days')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365)
                            ->default(30)
                            ->label('Default Warranty (Days)'),
                        Forms\Components\Toggle::make('require_customer_approval')
                            ->default(true)
                            ->label('Require Customer Approval'),
                        Forms\Components\Toggle::make('enable_quality_check')
                            ->default(true)
                            ->label('Enable Quality Check'),
                    ])->columns(2),

                Forms\Components\Section::make('Inventory Settings')
                    ->schema([
                        Forms\Components\Toggle::make('allow_negative_stock')
                            ->default(false)
                            ->label('Allow Negative Stock')
                            ->helperText('Warning: Can cause inventory discrepancies'),
                        Forms\Components\Toggle::make('track_serial_numbers')
                            ->default(true)
                            ->label('Track Serial Numbers'),
                        Forms\Components\Toggle::make('enable_low_stock_alerts')
                            ->default(true)
                            ->label('Enable Low Stock Alerts'),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->numeric()
                            ->minValue(1)
                            ->default(5)
                            ->label('Low Stock Threshold'),
                    ])->columns(2),

                Forms\Components\Section::make('Notifications')
                    ->schema([
                        Forms\Components\Toggle::make('email_notifications')
                            ->default(true)
                            ->label('Email Notifications'),
                        Forms\Components\Toggle::make('sms_notifications')
                            ->default(false)
                            ->label('SMS Notifications'),
                        Forms\Components\KeyValue::make('notification_events')
                            ->label('Notification Events')
                            ->keyLabel('Event')
                            ->valueLabel('Enabled')
                            ->addActionLabel('Add Event')
                            ->helperText('Configure which events trigger notifications'),
                    ])->columns(2),

                Forms\Components\Section::make('Terms & Conditions')
                    ->schema([
                        Forms\Components\Textarea::make('invoice_terms')
                            ->rows(3)
                            ->label('Invoice Terms')
                            ->placeholder('Terms and conditions printed on invoices...'),
                        Forms\Components\Textarea::make('repair_terms')
                            ->rows(3)
                            ->label('Repair Terms')
                            ->placeholder('Terms and conditions for repair tickets...'),
                        Forms\Components\Textarea::make('warranty_terms')
                            ->rows(3)
                            ->label('Warranty Terms')
                            ->placeholder('Warranty terms and conditions...'),
                    ])->columns(1),

                Forms\Components\Section::make('Setup Status')
                    ->schema([
                        Forms\Components\Placeholder::make('is_setup_complete')
                            ->label('Setup Complete')
                            ->content(fn (ShopProfile $record) => $record->isSetupComplete() ? '✅ Yes' : '❌ No'),
                        Forms\Components\Placeholder::make('setup_completed_at')
                            ->label('Setup Completed At')
                            ->content(fn (ShopProfile $record) => $record->setup_completed_at?->format('d/m/Y H:i') ?? 'Not completed'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->size(40)
                    ->placeholder(fn (ShopProfile $record) => mb_substr($record->business_name, 0, 1)),
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Currency')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_setup_complete')
                    ->label('Setup')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_setup_complete')
                    ->label('Setup Complete')
                    ->trueLabel('Complete')
                    ->falseLabel('Incomplete')
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListShopProfiles::route('/'),
            'create' => Pages\CreateShopProfile::route('/create'),
            'edit' => Pages\EditShopProfile::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant'])
            ->withoutGlobalScopes(); // Admin can see all shop profiles
    }
}
