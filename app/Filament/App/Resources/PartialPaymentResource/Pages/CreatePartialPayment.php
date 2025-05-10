<?php

namespace App\Filament\App\Resources\PartialPaymentResource\Pages;

use App\Filament\App\Resources\PartialPaymentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePartialPayment extends CreateRecord
{
    protected static string $resource = PartialPaymentResource::class;
}