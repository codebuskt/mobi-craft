<?php

namespace App\Filament\Resources\RepairTicketResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'devices';

    protected static ?string $title = 'Devices';

    protected static ?string $modelLabel = 'Device';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->sku})"),

                    Select::make('device_status')
                        ->options([
                            'received' => 'Received',
                            'diagnosing' => 'Diagnosing',
                            'waiting_approval' => 'Waiting for Approval',
                            'waiting_parts' => 'Waiting for Parts',
                            'in_repair' => 'In Repair',
                            'quality_check' => 'Quality Check',
                            'ready_delivery' => 'Ready for Delivery',
                            'delivered' => 'Delivered',
                        ])
                        ->required()
                        ->default('received')
                        ->native(false),
                ]),

                Grid::make(3)->schema([
                    TextInput::make('brand'),
                    TextInput::make('model'),
                    TextInput::make('color'),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('imei')
                        ->label('IMEI'),

                    TextInput::make('serial_number')
                        ->label('Serial Number'),
                ]),

                Select::make('condition')
                    ->options([
                        'new' => 'New',
                        'excellent' => 'Excellent',
                        'good' => 'Good',
                        'fair' => 'Fair',
                        'poor' => 'Poor',
                        'damaged' => 'Damaged',
                    ])
                    ->default('good')
                    ->native(false),

                Textarea::make('reported_issue')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('diagnosis_notes')
                    ->label('Diagnosis Notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('repair_notes')
                    ->label('Repair Notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('quality_check_notes')
                    ->label('Quality Check Notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('intake_photos')
                    ->label('Intake Photos')
                    ->multiple()
                    ->image()
                    ->directory('repairs/intake')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('diagnosis_photos')
                    ->label('Diagnosis Photos')
                    ->multiple()
                    ->image()
                    ->directory('repairs/diagnosis')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('repair_photos')
                    ->label('Repair Photos')
                    ->multiple()
                    ->image()
                    ->directory('repairs/repair')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('delivery_photos')
                    ->label('Delivery Photos')
                    ->multiple()
                    ->image()
                    ->directory('repairs/delivery')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('Device')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('identifier')
                    ->label('IMEI / Serial')
                    ->searchable()
                    ->placeholder('N/A'),

                BadgeColumn::make('device_status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'received',
                        'info' => 'diagnosing',
                        'warning' => 'waiting_approval',
                        'orange' => 'waiting_parts',
                        'primary' => 'in_repair',
                        'purple' => 'quality_check',
                        'success' => 'ready_delivery',
                        'success' => 'delivered',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('brand')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('model')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('condition')
                    ->badge()
                    ->colors([
                        'success' => 'new',
                        'success' => 'excellent',
                        'info' => 'good',
                        'warning' => 'fair',
                        'danger' => 'poor',
                        'danger' => 'damaged',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
