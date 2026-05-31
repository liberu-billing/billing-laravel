<?php

declare(strict_types=1);

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Filament\Client\Resources\InvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    #[\Override]
    protected static string $resource = InvoiceResource::class;
}
