<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\CustomerContact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Contacts';

    protected static ?string $icon = 'heroicon-o-user-group';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Full Name'),
                        Forms\Components\TextInput::make('designation')
                            ->maxLength(100)
                            ->label('Designation')
                            ->placeholder('e.g., Manager, Owner, Purchase Officer'),
                        Forms\Components\TextInput::make('department')
                            ->maxLength(100)
                            ->label('Department')
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Phone')
                            ->placeholder('+8801XXXXXXXXX'),
                        Forms\Components\TextInput::make('mobile')
                            ->tel()
                            ->maxLength(20)
                            ->label('Mobile')
                            ->placeholder('+8801XXXXXXXXX'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Email'),
                    ])->columns(2),

                Forms\Components\Section::make('Notification Preferences')
                    ->schema([
                        Forms\Components\Toggle::make('is_primary')
                            ->label('Primary Contact')
                            ->helperText('Only one primary contact per customer'),
                        Forms\Components\Toggle::make('receive_invoices')
                            ->default(true)
                            ->label('Receive Invoices'),
                        Forms\Components\Toggle::make('receive_repair_updates')
                            ->default(true)
                            ->label('Receive Repair Updates'),
                        Forms\Components\Toggle::make('receive_marketing')
                            ->default(false)
                            ->label('Receive Marketing'),
                    ])->columns(2),

                Forms\Components\Textarea::make('notes')
                    ->rows(2)
                    ->label('Notes'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('designation')
                    ->label('Designation')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department')
                    ->label('Department')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->placeholder('—')
                    ->copyable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->label('Mobile')
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('receive_invoices')
                    ->label('Invoices')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark'),
                Tables\Columns\IconColumn::make('receive_repair_updates')
                    ->label('Repair Updates')
                    ->boolean()
                    ->trueIcon('heroicon-o-wrench-screwdriver')
                    ->falseIcon('heroicon-o-x-mark'),
                Tables\Columns\IconColumn::make('receive_marketing')
                    ->label('Marketing')
                    ->boolean()
                    ->trueIcon('heroicon-o-megaphone')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}