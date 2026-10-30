<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getFormSchema(): array
    {
        return [
            Select::make('tenant_id')
                ->relationship('tenant', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('subscription_plan_id')
                ->relationship('subscriptionPlan', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                    'void' => 'Void',
                ])
                ->required(),

            DatePicker::make('period_start')
                ->label('Period Start')
                ->default(now()->startOfMonth()),

            DatePicker::make('period_end')
                ->label('Period End')
                ->default(now()->endOfMonth()),

            DatePicker::make('due_date')
                ->label('Due Date')
                ->default(fn () => now()->addMonth()->toDateString()),

            TextInput::make('subtotal')
                ->label('Subtotal (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->default(0),

            TextInput::make('tax_amount')
                ->label('Tax (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->default(0),

            TextInput::make('total_amount')
                ->label('Total (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->readonly(),

            TextInput::make('amount_due')
                ->label('Amount Due (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->readonly(),

            TextInput::make('payment_method')
                ->maxLength(50)
                ->nullable(),

            TextInput::make('payment_reference')
                ->maxLength(100)
                ->nullable(),

            DatePicker::make('paid_at')
                ->label('Paid At')
                ->nullable(),

            Textarea::make('notes')
                ->rows(3)
                ->maxLength(500),

            Textarea::make('terms_conditions')
                ->rows(3)
                ->maxLength(500)
                ->helperText('Payment terms and conditions'),

            Select::make('created_by')
                ->relationship('creator', 'name')
                ->searchable()
                ->preload(),
        ];
    }
}

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getFormSchema(): array
    {
        return [
            Select::make('tenant_id')
                ->relationship('tenant', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('subscription_plan_id')
                ->relationship('subscriptionPlan', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                    'void' => 'Void',
                ])
                ->required(),

            DatePicker::make('period_start')
                ->label('Period Start')
                ->default(now()->startOfMonth()),

            DatePicker::make('period_end')
                ->label('Period End')
                ->default(now()->endOfMonth()),

            DatePicker::make('due_date')
                ->label('Due Date')
                ->default(fn () => now()->addMonth()->toDateString()),

            TextInput::make('subtotal')
                ->label('Subtotal (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->default(0),

            TextInput::make('tax_amount')
                ->label('Tax (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->default(0),

            TextInput::make('total_amount')
                ->label('Total (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->readonly(),

            TextInput::make('amount_due')
                ->label('Amount Due (BDT)')
                ->numeric()
                ->step(0.01)
                ->prefix('৳')
                ->readonly(),

            TextInput::make('payment_method')
                ->maxLength(50)
                ->nullable(),

            TextInput::make('payment_reference')
                ->maxLength(100)
                ->nullable(),

            DatePicker::make('paid_at')
                ->label('Paid At')
                ->nullable(),

            Textarea::make('notes')
                ->rows(3)
                ->maxLength(500),

            Textarea::make('terms_conditions')
                ->rows(3)
                ->maxLength(500)
                ->helperText('Payment terms and conditions'),
        ];
    }
}