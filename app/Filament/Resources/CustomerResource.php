<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers\ContactsRelationManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Customers & Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Full Name'),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->tel()
                            ->maxLength(20)
                            ->label('Phone')
                            ->placeholder('+8801XXXXXXXXX')
                            ->helperText('Will be normalized to +8801XXXXXXXXX format'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Email')
                            ->placeholder('customer@example.com'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'walk_in' => 'Walk-in',
                                'regular' => 'Regular',
                                'corporate' => 'Corporate',
                                'wholesale' => 'Wholesale',
                            ])
                            ->default('regular')
                            ->required()
                            ->label('Customer Type'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'blocked' => 'Blocked',
                            ])
                            ->default('active')
                            ->required()
                            ->label('Status'),
                        Forms\Components\TextInput::make('code')
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Customer Code')
                            ->helperText('Auto-generated on creation'),
                    ])->columns(2),

                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\Textarea::make('address')
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
                            ->label('Postal Code'),
                        Forms\Components\TextInput::make('country')
                            ->default('Bangladesh')
                            ->maxLength(100)
                            ->label('Country'),
                    ])->columns(2),

                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('credit_limit')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->label('Credit Limit')
                            ->prefix('৳')
                            ->helperText('Maximum outstanding balance allowed'),
                        Forms\Components\TextInput::make('payment_terms_days')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365)
                            ->label('Payment Terms (Days)')
                            ->placeholder('Override tenant default')
                            ->helperText('Leave empty to use tenant default'),
                        Forms\Components\Placeholder::make('outstanding_balance')
                            ->label('Outstanding Balance')
                            ->content(fn (Customer $record) => $record->exists
                                ? '৳' . number_format($record->outstanding_balance, 2)
                                : '৳0.00 (new customer)'),
                        Forms\Components\Placeholder::make('available_credit')
                            ->label('Available Credit')
                            ->content(fn (Customer $record) => $record->exists
                                ? '৳' . number_format($record->available_credit, 2)
                                : '৳0.00'),
                        Forms\Components\Placeholder::make('lifetime_value')
                            ->label('Lifetime Value')
                            ->content(fn (Customer $record) => $record->exists
                                ? '৳' . number_format($record->lifetime_value, 2)
                                : '৳0.00'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->placeholder('Optional'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->label('Gender')
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('national_id')
                            ->maxLength(50)
                            ->label('National ID (NID)')
                            ->placeholder('For KYC compliance'),
                        Forms\Components\TextInput::make('source')
                            ->maxLength(50)
                            ->label('Source')
                            ->placeholder('e.g., walk_in, referral, online, social_media'),
                        Forms\Components\TextInput::make('referred_by')
                            ->maxLength(100)
                            ->label('Referred By')
                            ->placeholder('Optional'),
                        Forms\Components\KeyValue::make('tags')
                            ->label('Tags')
                            ->keyLabel('Tag')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Tag')
                            ->helperText('Custom tags for segmentation'),
                        Forms\Components\KeyValue::make('preferences')
                            ->label('Preferences')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Preference')
                            ->helperText('Communication preferences, etc.'),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->label('Notes')
                            ->placeholder('Internal notes about this customer...'),
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
                    ->copyable()
                    ->copyMessage('Customer code copied'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('Phone number copied'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'gray' => 'walk_in',
                        'primary' => 'regular',
                        'success' => 'corporate',
                        'warning' => 'wholesale',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'walk_in' => 'Walk-in',
                        'regular' => 'Regular',
                        'corporate' => 'Corporate',
                        'wholesale' => 'Wholesale',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                        'danger' => 'blocked',
                    ]),
                Tables\Columns\TextColumn::make('outstanding_balance')
                    ->label('Outstanding')
                    ->money('BDT', locale: 'bn')
                    ->sortable()
                    ->color(fn (Customer $record): string => $record->outstanding_balance > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->label('Credit Limit')
                    ->money('BDT', locale: 'bn')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_purchases')
                    ->label('Purchases')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_repairs')
                    ->label('Repairs')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_purchase_at')
                    ->label('Last Purchase')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'walk_in' => 'Walk-in',
                        'regular' => 'Regular',
                        'corporate' => 'Corporate',
                        'wholesale' => 'Wholesale',
                    ])
                    ->label('Customer Type')
                    ->native(false),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'blocked' => 'Blocked',
                    ])
                    ->label('Status')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('outstanding_balance')
                    ->label('Has Outstanding Balance')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->native(false)
                    ->queries(
                        true: fn (Builder $query) => $query->where('outstanding_balance', '>', 0),
                        false: fn (Builder $query) => $query->where('outstanding_balance', '<=', 0),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('view_sales')
                        ->label('Sales')
                        ->icon('heroicon-o-shopping-bag')
                        ->url(fn (Customer $record) => route('filament.admin.resources.sales.index', ['customer_id' => $record->id])),
                    Tables\Actions\Action::make('view_repairs')
                        ->label('Repairs')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->url(fn (Customer $record) => route('filament.admin.resources.repair-tickets.index', ['customer_id' => $record->id])),
                    Tables\Actions\Action::make('view_payments')
                        ->label('Payments')
                        ->icon('heroicon-o-currency-dollar')
                        ->url(fn (Customer $record) => route('filament.admin.resources.customer-payments.index', ['customer_id' => $record->id])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant', 'primaryContact'])
            ->withoutGlobalScopes(); // Admin can see all customers
    }
}