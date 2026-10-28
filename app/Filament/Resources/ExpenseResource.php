<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Expenses';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expense Information')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_path),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('BDT')
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'mobile_banking' => 'Mobile Banking',
                                'bank_transfer' => 'Bank Transfer',
                                'cheque' => 'Cheque',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(100),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\FileUpload::make('attachment')
                            ->directory('expenses')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(2),
                    ])->columns(2),

                Forms\Components\Section::make('Approval')
                    ->schema([
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Approved By')
                            ->visible(fn (Forms\Get $get): bool => in_array($get('status'), ['approved', 'rejected'])),
                    ])->columns(1),

                Forms\Components\Section::make('Recurring Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Recurring Expense')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if (! $state) {
                                    $set('recurring_frequency', null);
                                    $set('next_due_date', null);
                                }
                            }),
                        Forms\Components\Select::make('recurring_frequency')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'yearly' => 'Yearly',
                            ])
                            ->visible(fn (Forms\Get $get): bool => $get('is_recurring')),
                        Forms\Components\DatePicker::make('next_due_date')
                            ->label('Next Due Date')
                            ->visible(fn (Forms\Get $get): bool => $get('is_recurring')),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('category.full_path')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('BDT', locale: 'bn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'mobile_banking' => 'Mobile Banking',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->colors([
                        'success' => 'cash',
                        'primary' => 'card',
                        'info' => 'mobile_banking',
                        'warning' => 'bank_transfer',
                        'gray' => 'cheque',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('Recurring')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('next_due_date')
                    ->label('Next Due')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'mobile_banking' => 'Mobile Banking',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                    ]),
                Tables\Filters\Filter::make('recurring')
                    ->label('Recurring Only')
                    ->query(fn (Builder $query): Builder => $query->where('is_recurring', true)),
                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Expense $record): bool => $record->canBeApproved())
                    ->requiresConfirmation()
                    ->action(fn (Expense $record) => $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                    ])),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Expense $record): bool => $record->canBeApproved())
                    ->requiresConfirmation()
                    ->action(fn (Expense $record) => $record->update([
                        'status' => 'rejected',
                        'approved_by' => auth()->id(),
                    ])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records
                            ->filter(fn ($r) => $r->canBeApproved())
                            ->each->update([
                                'status' => 'approved',
                                'approved_by' => auth()->id(),
                            ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
