<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerDueResource\Pages;
use App\Models\CustomerDue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerDueResource extends Resource
{
    protected static ?string $model = CustomerDue::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Due Information')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->name} (Due: {$record->outstanding_balance})"),
                        Forms\Components\Select::make('sale_id')
                            ->relationship('sale', 'sale_number')
                            ->searchable()
                            ->preload()
                            ->label('Source Sale')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sale_number} (Balance: {$record->balance_amount})")
                            ->disabled(fn (Forms\Get $get): bool => ! $get('customer_id')),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->default(now()->addDays(30)),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Payment Tracking')
                    ->schema([
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('balance_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->url(fn (CustomerDue $record): string => $record->customer ? route('filament.admin.resources.customers.view', $record->customer) : '#'),
                Tables\Columns\TextColumn::make('sale.sale_number')
                    ->label('Source Sale')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->url(fn (CustomerDue $record): string => $record->sale ? route('filament.admin.resources.sales.view', $record->sale) : '#'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn (CustomerDue $record): string => $record->isOverdue() ? 'danger' : ''),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'partial',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('aging_bucket')
                    ->label('Aging')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('BDT', locale: 'bn')
                    ->label('Paid')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance_amount')
                    ->money('BDT', locale: 'bn')
                    ->label('Balance')
                    ->sortable()
                    ->color(fn (float $state): string => $state > 0 ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Customer'),
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query): Builder => $query->where('status', '!=', 'paid')
                        ->where('due_date', '<', now()->toDateString())),
                Tables\Filters\Filter::make('pending')
                    ->label('Pending')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['pending', 'partial'])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('record_payment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn (CustomerDue $record): bool => $record->balance_amount > 0 && $record->status !== 'paid')
                    ->url(fn (CustomerDue $record): string => route('filament.admin.resources.customer-payments.create', ['customer' => $record->customer_id, 'sale' => $record->sale_id])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerDues::route('/'),
            'create' => Pages\CreateCustomerDue::route('/create'),
            'view' => Pages\ViewCustomerDue::route('/{record}'),
            'edit' => Pages\EditCustomerDue::route('/{record}/edit'),
        ];
    }
}
