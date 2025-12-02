<?php

namespace App\Filament\Admin\Resources\PaymentGateways;

use App\Filament\Admin\Resources\PaymentGateways\Pages\CreatePaymentGateway;
use App\Filament\Admin\Resources\PaymentGateways\Pages\EditPaymentGateway;
use App\Filament\Admin\Resources\PaymentGateways\Pages\ListPaymentGateways;
use App\Filament\Admin\Resources\PaymentGateways\Schemas\PaymentGatewayForm;
use App\Filament\Admin\Resources\PaymentGateways\Tables\PaymentGatewaysTable;
use App\Models\PaymentGateway;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return PaymentGatewayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentGatewaysTable::configure($table);
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
            'index' => ListPaymentGateways::route('/'),
            'create' => CreatePaymentGateway::route('/create'),
            'edit' => EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}
