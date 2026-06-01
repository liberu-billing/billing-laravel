<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\PartialPayments\Pages;

use App\Filament\App\Resources\PartialPayments\PartialPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartialPayment extends CreateRecord
{
    #[\Override]
    protected static string $resource = PartialPaymentResource::class;
}
