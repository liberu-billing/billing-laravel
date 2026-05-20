<?php

namespace App\Filament\Admin\Resources\PaymentGateways\Pages;

use App\Filament\Admin\Resources\PaymentGateways\PaymentGatewayResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentGateway extends EditRecord
{
    protected static string $resource = PaymentGatewayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
