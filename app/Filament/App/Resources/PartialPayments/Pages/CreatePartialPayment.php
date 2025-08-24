<?php

namespace App\Filament\App\Resources\PartialPayments\Pages;

use App\Filament\App\Resources\PartialPayments\PartialPaymentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePartialPayment extends CreateRecord
{
    protected static string $resource = PartialPaymentResource::class;
}