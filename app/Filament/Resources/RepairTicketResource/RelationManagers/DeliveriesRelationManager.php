<?php

namespace App\Filament\Resources\RepairTicketResource\RelationManagers;

use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';

    protected static ?string $title = 'Deliveries';

    protected static ?string $modelLabel = 'Delivery';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('repair_device_id')
                        ->label('Device')
                        ->relationship('repairDevice', 'display_name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Select::make('delivered_by')
                        ->label('Delivered By')
                        ->relationship('deliverer', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"),
                ]),

                Grid::make(2)->schema([
                    DatePicker::make('delivered_date')
                        ->required()
                        ->default(now()),

                    Select::make('received_by_customer_id')
                        ->label('Received By Customer')
                        ->relationship('receiver', 'name')
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"),
                ]),

                Textarea::make('condition_on_delivery')
                    ->label('Condition on Delivery')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('customer_notes')
                    ->label('Customer Notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('delivery_photos')
                    ->label('Delivery Photos')
                    ->multiple()
                    ->image()
                    ->directory('repairs/delivery')
                    ->columnSpanFull(),

                TextInput::make('customer_signature')
                    ->label('Customer Signature (Base64)')
                    ->columnSpanFull()
                    ->helperText('Base64 encoded signature image'),

                Grid::make(3)->schema([
                    DatePicker::make('warranty_start_date')
                        ->label('Warranty Start'),

                    DatePicker::make('warranty_end_date')
                        ->label('Warranty End'),

                    TextInput::make('warranty_months')
                        ->label('Warranty (Months)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ]),

                Textarea::make('warranty_terms')
                    ->label('Warranty Terms')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('repairDevice.display_name')
                    ->label('Device')
                    ->searchable(),

                TextColumn::make('delivered_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('deliverer.name')
                    ->label('Delivered By')
                    ->searchable(),

                TextColumn::make('receiver.name')
                    ->label('Received By')
                    ->placeholder('Customer')
                    ->searchable(),

                IconColumn::make('customer_signature')
                    ->label('Signed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn ($record) => ! empty($record->customer_signature)),

                TextColumn::make('warranty_end_date')
                    ->label('Warranty Until')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state && now()->lte($state) ? 'success' : 'danger')
                    ->placeholder('No warranty'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['tenant_id'] = auth()->user()->tenant_id ?? Tenant::getCurrentTenant()?->id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
