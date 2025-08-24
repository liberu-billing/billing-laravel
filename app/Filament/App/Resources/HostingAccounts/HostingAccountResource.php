<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\HostingAccountResource\Pages\ListHostingAccounts;
use App\Filament\App\Resources\HostingAccountResource\Pages\CreateHostingAccount;
use App\Filament\App\Resources\HostingAccountResource\Pages\EditHostingAccount;
use App\Filament\App\Resources\HostingAccountResource\Pages;
use App\Models\HostingAccount;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class HostingAccountResource extends Resource
{
    protected static ?string $model = HostingAccount::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-server';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->required(),
                Select::make('control_panel')
                    ->options([
                        'cpanel' => 'cPanel',
                        'plesk' => 'Plesk',
                        'directadmin' => 'DirectAdmin',
                    ])
                    ->required(),
                TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                TextInput::make('package')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
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
                TextColumn::make('customer.name'),
                TextColumn::make('subscription.id'),
                TextColumn::make('control_panel'),
                TextColumn::make('username'),
                TextColumn::make('domain'),
                TextColumn::make('package'),
                TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
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
            'index' => ListHostingAccounts::route('/'),
            'create' => CreateHostingAccount::route('/create'),
            'edit' => EditHostingAccount::route('/{record}/edit'),
        ];
    }
}
