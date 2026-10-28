<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepairTicketResource\Pages;
use App\Filament\Resources\RepairTicketResource\RelationManagers;
use App\Models\RepairTicket;
use Filament\Forms\Components\DatePicker;
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

class RepairTicketResource extends Resource
{
    protected static ?string $model = RepairTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Repair Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->phone})"),

                    Select::make('assigned_technician_id')
                        ->label('Assigned Technician')
                        ->relationship('assignedTechnician', 'name')
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"),
                ]),

                Grid::make(3)->schema([
                    Select::make('status')
                        ->options([
                            'received' => 'Received',
                            'diagnosing' => 'Diagnosing',
                            'waiting_approval' => 'Waiting for Approval',
                            'waiting_parts' => 'Waiting for Parts',
                            'in_repair' => 'In Repair',
                            'quality_check' => 'Quality Check',
                            'ready_delivery' => 'Ready for Delivery',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                            'rejected' => 'Rejected',
                        ])
                        ->required()
                        ->default('received')
                        ->native(false),

                    Select::make('priority')
                        ->options([
                            'low' => 'Low',
                            'normal' => 'Normal',
                            'high' => 'High',
                            'urgent' => 'Urgent',
                        ])
                        ->required()
                        ->default('normal')
                        ->native(false),

                    TextInput::make('ticket_number')
                        ->label('Ticket Number')
                        ->readonly()
                        ->unique(ignoreRecord: true),
                ]),

                Grid::make(3)->schema([
                    DatePicker::make('received_date')
                        ->required()
                        ->default(now()),

                    DatePicker::make('estimated_completion_date')
                        ->label('Estimated Completion'),

                    DatePicker::make('delivered_date')
                        ->label('Delivered Date'),
                ]),

                Grid::make(4)->schema([
                    TextInput::make('diagnostic_fee')
                        ->label('Diagnostic Fee')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01),

                    TextInput::make('labor_charge')
                        ->label('Labor Charge')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01),

                    TextInput::make('parts_cost')
                        ->label('Parts Cost')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01),

                    TextInput::make('total_charged')
                        ->label('Total Charged')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01)
                        ->readonly(),
                ]),

                Grid::make(3)->schema([
                    TextInput::make('paid_amount')
                        ->label('Paid Amount')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01),

                    TextInput::make('balance_amount')
                        ->label('Balance')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01)
                        ->readonly(),

                    TextInput::make('warranty_months')
                        ->label('Warranty (Months)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ]),

                Textarea::make('reported_issue')
                    ->label('Reported Issue')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('intake_notes')
                    ->label('Intake Notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('warranty_terms')
                    ->label('Warranty Terms')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->customer?->name ?? 'Walk-in'),

                TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'received',
                        'info' => 'diagnosing',
                        'warning' => 'waiting_approval',
                        'orange' => 'waiting_parts',
                        'primary' => 'in_repair',
                        'purple' => 'quality_check',
                        'success' => 'ready_delivery',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                BadgeColumn::make('priority')
                    ->colors([
                        'gray' => 'low',
                        'info' => 'normal',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ]),

                TextColumn::make('assignedTechnician.name')
                    ->label('Technician')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Unassigned'),

                TextColumn::make('total_charged')
                    ->label('Total')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('BDT')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('received_date')
                    ->label('Received')
                    ->date()
                    ->sortable(),

                TextColumn::make('estimated_completion_date')
                    ->label('Est. Completion')
                    ->date()
                    ->sortable()
                    ->color(fn ($state, $record) => $record->isOverdue() ? 'danger' : null
                    ),

                TextColumn::make('delivered_date')
                    ->label('Delivered')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'received' => 'Received',
                        'diagnosing' => 'Diagnosing',
                        'waiting_approval' => 'Waiting for Approval',
                        'waiting_parts' => 'Waiting for Parts',
                        'in_repair' => 'In Repair',
                        'quality_check' => 'Quality Check',
                        'ready_delivery' => 'Ready for Delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                SelectFilter::make('assigned_technician_id')
                    ->relationship('assignedTechnician', 'name')
                    ->label('Technician')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query): Builder => $query->where('estimated_completion_date', '<', now()->toDateString())
                        ->whereNotIn('status', ['delivered', 'cancelled', 'rejected'])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('transition_status')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('new_status')
                            ->label('New Status')
                            ->options(fn ($record) => array_combine(
                                array_keys($record::getValidTransitions()[$record->status] ?? []),
                                array_map(fn ($s) => ucfirst(str_replace('_', ' ', $s)), array_keys($record::getValidTransitions()[$record->status] ?? []))
                            ))
                            ->required()
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function (array $data, RepairTicket $record) {
                        $record->transitionTo($data['new_status'], $data['notes'] ?? null);
                    })
                    ->visible(fn ($record) => ! in_array($record->status, ['delivered', 'cancelled', 'rejected'])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('received_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DevicesRelationManager::class,
            RelationManagers\AccessoriesRelationManager::class,
            RelationManagers\DiagnosesRelationManager::class,
            RelationManagers\PartsRelationManager::class,
            RelationManagers\QualityChecksRelationManager::class,
            RelationManagers\DeliveriesRelationManager::class,
            RelationManagers\StatusHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepairTickets::route('/'),
            'create' => Pages\CreateRepairTicket::route('/create'),
            'edit' => Pages\EditRepairTicket::route('/{record}/edit'),
            'view' => Pages\ViewRepairTicket::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer', 'assignedTechnician']);
    }
}
