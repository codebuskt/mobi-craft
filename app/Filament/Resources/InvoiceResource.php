<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Tenancy';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->maxLength(255)
                            ->disabled(), // Generated automatically
                        Forms\Components\Select::make('tenant_id')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('subscription_plan_id')
                            ->relationship('subscriptionPlan', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'void' => 'Void',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (BDT)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('৳')
                            ->default(0),
                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Tax (15% VAT)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('৳')
                            ->default(0),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total (BDT)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('৳')
                            ->readonly(),
                        Forms\Components\TextInput::make('amount_due')
                            ->label('Amount Due (BDT)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('৳')
                            ->readonly(),
                    ])->columns(2),

                Forms\Components\Section::make('Billing Period')
                    ->schema([
                        Forms\Components\DatePicker::make('period_start')
                            ->label('Period Start')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('period_end')
                            ->label('Period End')
                            ->default(now()->endOfMonth()),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->default(fn () => now()->addMonth()->toDateString()),
                    ]),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\TextInput::make('payment_method')
                            ->maxLength(50)
                            ->nullable(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(100)
                            ->nullable(),
                        Forms\Components\DatePicker::make('paid_at')
                            ->label('Paid At')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('terms_conditions')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->helperText('Payment terms and conditions'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'overdue',
                        'gray' => 'draft',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (BDT)')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->label('Due (BDT)')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('isOverdue')
                    ->label('Overdue')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'void' => 'Void',
                    ])
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant'),
                Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->relationship('subscriptionPlan', 'name')
                    ->label('Plan'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Invoice $record) {
                        return $record->status !== 'paid' && $record->amount_due > 0;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/),
            'create' => Pages\CreateInvoice::route('/create),
            'edit' => Pages\EditInvoice::route('/{record}/edit),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}