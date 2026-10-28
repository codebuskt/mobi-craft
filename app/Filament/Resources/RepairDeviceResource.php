<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepairDeviceResource\Pages;
use App\Filament\Resources\RepairDeviceResource\RelationManagers;
use App\Models\RepairDevice;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RepairDeviceResource extends Resource
{
    protected static ?string $model = RepairDevice::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Repair Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('repair_ticket_id')
                        ->label('Repair Ticket')
                        ->relationship('repairTicket', 'ticket_number')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->ticket_number} - {$record->customer->name}"),

                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->sku})"),
                ]),

                Grid::make(2)->schema([
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('Device')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('repairTicket.ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('repairTicket.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Walk-in'),

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

                BadgeColumn::make('condition')
                    ->colors([
                        'success' => 'new',
                        'success' => 'excellent',
                        'info' => 'good',
                        'warning' => 'fair',
                        'danger' => 'poor',
                        'danger' => 'damaged',
                    ]),
            ])
            ->filters([
                SelectFilter::make('device_status')
                    ->options([
                        'received' => 'Received',
                        'diagnosing' => 'Diagnosing',
                        'waiting_approval' => 'Waiting for Approval',
                        'waiting_parts' => 'Waiting for Parts',
                        'in_repair' => 'In Repair',
                        'quality_check' => 'Quality Check',
                        'ready_delivery' => 'Ready for Delivery',
                        'delivered' => 'Delivered',
                    ]),

                SelectFilter::make('repair_ticket_id')
                    ->relationship('repairTicket', 'ticket_number')
                    ->label('Ticket')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AccessoriesRelationManager::class,
            RelationManagers\DiagnosesRelationManager::class,
            RelationManagers\PartsRelationManager::class,
            RelationManagers\QualityChecksRelationManager::class,
            RelationManagers\DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepairDevices::route('/'),
            'create' => Pages\CreateRepairDevice::route('/create'),
            'edit' => Pages\EditRepairDevice::route('/{record}/edit'),
            'view' => Pages\ViewRepairDevice::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['repairTicket', 'repairTicket.customer', 'product']);
    }
}
