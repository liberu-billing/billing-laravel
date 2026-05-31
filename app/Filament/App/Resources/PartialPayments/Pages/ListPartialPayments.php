<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\PartialPayments\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\PartialPayments\PartialPaymentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartialPayments extends ListRecords
{
    #[\Override]
    protected static string $resource = PartialPaymentResource::class;

    #[\Override]
    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
