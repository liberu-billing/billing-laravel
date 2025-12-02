<?php

namespace App\Filament\Admin\Resources\PaymentGateways\Pages;

use App\Filament\Admin\Resources\PaymentGateways\PaymentGatewayResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentGateway extends CreateRecord
{
    protected static string $resource = PaymentGatewayResource::class;
}
