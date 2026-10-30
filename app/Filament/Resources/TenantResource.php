<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Tenancy';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Unique identifier for subdomain (e.g., shop1)'),
                        Forms\Components\TextInput::make('domain')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('shop.mobicraft.com')
                            ->helperText('Optional custom domain'),
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('tenants/logos')
                            ->maxSize(1024)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Business Profile')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Setting')
                            ->columnSpanFull()
                            ->helperText('JSON settings: address, phone, email, currency, timezone, etc.'),
                    ])->collapsible(),

                Forms\Components\Section::make('Subscription')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'trial' => 'Trial',
                                'suspended' => 'Suspended',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->default('trial')
                            ->native(false),
                        Forms\Components\Select::make('plan_id')
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Subscription Plan'),
                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Trial Ends At'),
                        Forms\Components\DateTimePicker::make('subscription_ends_at')
                            ->label('Subscription Ends At'),
                    ])->columns(2),

                Forms\Components\Section::make('Billing')
                    ->schema([
                        Forms\Components\TextInput::make('current_period_start')
                            ->label('Current Period Start')
                            ->date()
                            ->default(now()->startOfMonth())
                            ->readonly(),
                        Forms\Components\TextInput::make('current_period_end')
                            ->label('Current Period End')
                            ->date()
                            ->default(now()->endOfMonth())
                            ->readonly(),
                        Forms\Components\TextInput::make('invoice_count')
                            ->label('Invoice Count')
                            ->numeric()
                            ->default(0)
                            ->readonly(),
                        Forms\Components\TextInput::make('amount_due')
                            ->label('Amount Due (BDT)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('৳')
                            ->default(0)
                            ->readonly(),
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
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Subdomain')
                    ->formatStateUsing(fn (string $state) => $state.'.mobicraft.test')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('domain')
                    ->label('Custom Domain')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'trial',
                        'danger' => 'suspended',
                        'gray' => 'expired',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial Ends')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subscription_ends_at')
                    ->label('Subscription Ends')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'trial' => 'Trial',
                        'suspended' => 'Suspended',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('plan_id')
                    ->relationship('plan', 'name')
                    ->label('Plan'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
