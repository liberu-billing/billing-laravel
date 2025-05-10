<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\HostingAccountResource\Pages;
use App\Models\HostingAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class HostingAccountResource extends Resource
{
    protected static ?string $model = HostingAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Forms\Components\Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->required(),
                Forms\Components\Select::make('control_panel')
                    ->options([
                        'cpanel' => 'cPanel',
                        'plesk' => 'Plesk',
                        'directadmin' => 'DirectAdmin',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('package')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name'),
                Tables\Columns\TextColumn::make('subscription.id'),
                Tables\Columns\TextColumn::make('control_panel'),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('domain'),
                Tables\Columns\TextColumn::make('package'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListHostingAccounts::route('/'),
            'create' => Pages\CreateHostingAccount::route('/create'),
            'edit' => Pages\EditHostingAccount::route('/{record}/edit'),
        ];
    }
}
