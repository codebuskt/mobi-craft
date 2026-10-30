<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Products & Inventory';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->relationship('parent', 'name')
                            ->label('Parent Category')
                            ->searchable()
                            ->preload()
                            ->placeholder('Root category (no parent)')
                            ->helperText('Leave empty for top-level category'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Category Name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->label('Slug (URL)')
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name, used in URLs'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->label('Description')
                            ->placeholder('Brief description of this category...'),
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(50)
                            ->label('Icon')
                            ->placeholder('heroicon-o-cube, heroicon-o-device-mobile, etc.')
                            ->helperText('Heroicon name or custom CSS class'),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('categories')
                            ->maxSize(1024)
                            ->label('Category Image')
                            ->helperText('Recommended: 400x300px'),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active')
                            ->helperText('Inactive categories are hidden from POS and product creation'),
                        Forms\Components\Toggle::make('is_service')
                            ->default(false)
                            ->label('Service Category')
                            ->helperText('Enable for repair services, labor charges, etc.'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order')
                            ->helperText('Lower values appear first'),
                    ])->columns(2),

                Forms\Components\Section::make('SEO (Optional)')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(60)
                            ->label('Meta Title')
                            ->placeholder('Leave empty to use category name'),
                        Forms\Components\Textarea::make('meta_description')
                            ->rows(2)
                            ->maxLength(160)
                            ->label('Meta Description')
                            ->placeholder('Brief description for search engines...'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->formatStateUsing(fn (Category $record): string => $record->path),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'product',
                        'success' => 'service',
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Service' : 'Product'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable()
                    ->badge()
                    ->color('info'),
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
                Tables\Filters\SelectFilter::make('is_service')
                    ->options([
                        '0' => 'Products',
                        '1' => 'Services',
                    ])
                    ->label('Category Type')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant', 'parent', 'children'])
            ->withoutGlobalScopes(); // Admin can see all categories
    }
}
