<?php

namespace App\Filament\App\Resources\PartialPayments\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\PartialPayments\PartialPaymentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartialPayments extends ListRecords
{
    protected static string $resource = PartialPaymentResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}