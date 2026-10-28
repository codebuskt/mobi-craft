<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Products & Inventory';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Category')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select category'),
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->label('Brand')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select brand'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Product Name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->label('Slug (URL)')
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name'),
                        Forms\Components\TextInput::make('sku')
                            ->maxLength(100)
                            ->label('SKU')
                            ->placeholder('Auto-generated if empty')
                            ->helperText('Stock Keeping Unit - unique identifier'),
                        Forms\Components\TextInput::make('barcode')
                            ->maxLength(100)
                            ->label('Barcode (EAN/UPC)')
                            ->placeholder('Optional - for barcode scanning'),
                    ])->columns(2),

                Forms\Components\Section::make('Type & Status')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'phone' => 'Mobile Phone',
                                'accessory' => 'Accessory',
                                'spare_part' => 'Spare Part',
                                'service' => 'Service',
                                'used_device' => 'Used Device',
                            ])
                            ->default('accessory')
                            ->required()
                            ->label('Product Type'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'discontinued' => 'Discontinued',
                            ])
                            ->default('draft')
                            ->required()
                            ->label('Status'),
                        Forms\Components\Toggle::make('is_pos_visible')
                            ->default(true)
                            ->label('Visible in POS')
                            ->helperText('Show this product in Point of Sale'),
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false)
                            ->label('Featured')
                            ->helperText('Highlight on dashboard/catalog'),
                        Forms\Components\Toggle::make('is_serialized')
                            ->default(false)
                            ->label('Serialized/IMEI Tracked')
                            ->helperText('Track individual units by serial/IMEI'),
                        Forms\Components\Toggle::make('track_imei')
                            ->default(false)
                            ->label('Track IMEI')
                            ->helperText('For mobile phones - enable IMEI validation'),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Cost Price')
                            ->prefix('৳')
                            ->helperText('Purchase/manufacturing cost'),
                        Forms\Components\TextInput::make('sale_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Sale Price')
                            ->prefix('৳')
                            ->helperText('Regular selling price'),
                        Forms\Components\TextInput::make('wholesale_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->label('Wholesale Price')
                            ->prefix('৳')
                            ->placeholder('Optional - for bulk sales'),
                        Forms\Components\TextInput::make('special_price')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->label('Special/Promo Price')
                            ->prefix('৳')
                            ->placeholder('Optional - temporary discount'),
                        Forms\Components\DateTimePicker::make('special_price_start')
                            ->label('Special Price Start'),
                        Forms\Components\DateTimePicker::make('special_price_end')
                            ->label('Special Price End'),
                        Forms\Components\TextInput::make('tax_rate')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->label('Tax Rate (%)')
                            ->suffix('%')
                            ->placeholder('Override tenant default'),
                        Forms\Components\Toggle::make('tax_inclusive')
                            ->default(false)
                            ->label('Tax Inclusive')
                            ->helperText('If enabled, prices include tax'),
                    ])->columns(2),

                Forms\Components\Section::make('Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Current Stock')
                            ->helperText('Physical quantity on hand'),
                        Forms\Components\TextInput::make('reserved_quantity')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Reserved')
                            ->helperText('Reserved for pending orders'),
                        Forms\Components\Placeholder::make('available_quantity')
                            ->label('Available')
                            ->content(fn (Product $record) => '৳'.number_format($record->available_quantity, 2))
                            ->visible(fn (Product $record) => $record->exists),
                        Forms\Components\TextInput::make('reorder_level')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Reorder Level')
                            ->helperText('Alert when stock falls below this'),
                        Forms\Components\TextInput::make('reorder_quantity')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Reorder Quantity')
                            ->helperText('Quantity to order when reordering'),
                        Forms\Components\TextInput::make('max_stock_level')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->label('Max Stock Level')
                            ->placeholder('Optional - maximum stock capacity'),
                        Forms\Components\TextInput::make('unit')
                            ->maxLength(20)
                            ->default('pcs')
                            ->label('Unit')
                            ->placeholder('e.g., pcs, box, pack, meter'),
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->step(0.001)
                            ->minValue(0)
                            ->label('Weight (kg)')
                            ->placeholder('Optional'),
                        Forms\Components\KeyValue::make('dimensions')
                            ->label('Dimensions (cm)')
                            ->keyLabel('Dimension')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Dimension')
                            ->placeholder('length, width, height')
                            ->helperText('e.g., length: 15, width: 7.5, height: 0.8'),
                    ])->columns(2),

                Forms\Components\Section::make('Warranty')
                    ->schema([
                        Forms\Components\TextInput::make('warranty_months')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(600)
                            ->default(0)
                            ->label('Warranty (Months)')
                            ->helperText('0 = no warranty'),
                        Forms\Components\TextInput::make('warranty_type')
                            ->maxLength(50)
                            ->label('Warranty Type')
                            ->placeholder('manufacturer, seller, extended'),
                    ])->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('products/thumbnails')
                            ->maxSize(512)
                            ->label('Main Image')
                            ->helperText('Recommended: 400x400px, square'),
                        Forms\Components\FileUpload::make('images')
                            ->image()
                            ->multiple()
                            ->directory('products/gallery')
                            ->maxSize(1024)
                            ->maxFiles(10)
                            ->label('Additional Images')
                            ->reorderable()
                            ->helperText('Drag to reorder. Max 10 images.'),
                    ])->columns(2),

                Forms\Components\Section::make('Descriptions')
                    ->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->rows(3)
                            ->maxLength(500)
                            ->label('Short Description')
                            ->placeholder('Brief summary for listings...'),
                        Forms\Components\Textarea::make('description')
                            ->rows(5)
                            ->label('Full Description')
                            ->placeholder('Detailed product description...'),
                        Forms\Components\KeyValue::make('specifications')
                            ->label('Technical Specifications')
                            ->keyLabel('Spec')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Specification')
                            ->placeholder('Key: Value pairs (e.g., RAM: 8GB, Storage: 128GB)')
                            ->helperText('Used for comparison and filtering'),
                    ])->columns(1),

                Forms\Components\Section::make('SEO (Optional)')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(60)
                            ->label('Meta Title'),
                        Forms\Components\Textarea::make('meta_description')
                            ->rows(2)
                            ->maxLength(160)
                            ->label('Meta Description'),
                        Forms\Components\KeyValue::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->keyLabel('Keyword')
                            ->valueLabel('')
                            ->addActionLabel('Add Keyword')
                            ->placeholder('product, mobile, phone'),
                    ])->columns(1),

                Forms\Components\Section::make('Advanced')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order'),
                        Forms\Components\TextInput::make('preferred_supplier_id')
                            ->maxLength(100)
                            ->label('Preferred Supplier Reference')
                            ->placeholder('Supplier code or ID'),
                        Forms\Components\TextInput::make('lead_time_days')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->label('Lead Time (Days)')
                            ->helperText('Days to restock from supplier'),
                        Forms\Components\KeyValue::make('attributes')
                            ->label('Custom Attributes')
                            ->keyLabel('Attribute')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Attribute')
                            ->placeholder('color: blue, storage: 128GB, size: M')
                            ->helperText('Flexible attributes for variants and filtering'),
                        Forms\Components\KeyValue::make('settings')
                            ->label('Additional Settings')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Setting')
                            ->placeholder('Custom key-value settings'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Image')
                    ->circular()
                    ->size(40)
                    ->placeholder(fn (Product $record) => mb_substr($record->name, 0, 1)),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('SKU copied'),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Barcode copied'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(40),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'phone',
                        'info' => 'accessory',
                        'warning' => 'spare_part',
                        'success' => 'service',
                        'gray' => 'used_device',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'phone' => 'Phone',
                        'accessory' => 'Accessory',
                        'spare_part' => 'Spare Part',
                        'service' => 'Service',
                        'used_device' => 'Used Device',
                        default => ucfirst($state),
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                        'danger' => 'discontinued',
                        'warning' => 'draft',
                    ]),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Cost')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Sale Price')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_sale_price')
                    ->label('Effective Price')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->color('success')
                    ->description(fn (Product $record): ?string => $record->isOnSpecial() ? 'Special Offer' : null),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn (Product $record): string => $record->isOutOfStock() ? 'danger' : ($record->isLowStock() ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('reserved_quantity')
                    ->label('Reserved')
                    ->numeric(2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('Available')
                    ->numeric(2)
                    ->sortable()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_pos_visible')
                    ->label('POS')
                    ->boolean()
                    ->trueIcon('heroicon-o-shopping-bag')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_serialized')
                    ->label('Serialized')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand')
                    ->searchable()
                    ->preload()
                    ->native(false),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'phone' => 'Mobile Phone',
                        'accessory' => 'Accessory',
                        'spare_part' => 'Spare Part',
                        'service' => 'Service',
                        'used_device' => 'Used Device',
                    ])
                    ->label('Type')
                    ->native(false),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'discontinued' => 'Discontinued',
                        'draft' => 'Draft',
                    ])
                    ->label('Status')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_pos_visible')
                    ->label('POS Visible')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_serialized')
                    ->label('Serialized')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('track_imei')
                    ->label('Track IMEI')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(fn (Product $record) => self::duplicateProduct($record))
                        ->requiresConfirmation()
                        ->modalHeading('Duplicate Product')
                        ->modalDescription('Create a copy of this product with a new SKU.'),
                    Tables\Actions\Action::make('stock_adjust')
                        ->label('Adjust Stock')
                        ->icon('heroicon-o-arrow-path')
                        ->url(fn (Product $record) => route('filament.admin.resources.stock-movements.create', ['product_id' => $record->id])),
                ]),
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
                    Tables\Actions\BulkAction::make('enable_pos')
                        ->label('Enable POS')
                        ->icon('heroicon-o-shopping-bag')
                        ->action(fn (Collection $records) => $records->each->update(['is_pos_visible' => true])),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->persistColumnSearchesInSession();

    }

    public static function duplicateProduct(Product $product): Product
    {
        $newProduct = $product->replicate();
        $newProduct->sku = null; // Will be auto-generated
        $newProduct->barcode = null;
        $newProduct->name = $product->name.' (Copy)';
        $newProduct->slug = null; // Will be auto-generated from name
        $newProduct->status = 'draft';
        $newProduct->stock_quantity = 0;
        $newProduct->reserved_quantity = 0;
        $newProduct->save();

        return $newProduct;
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant', 'category', 'brand'])
            ->withoutGlobalScopes(); // Admin can see all products
    }
}
