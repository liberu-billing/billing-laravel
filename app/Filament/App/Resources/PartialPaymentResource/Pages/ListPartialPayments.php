<?php

namespace App\Filament\App\Resources\PartialPaymentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\PartialPaymentResource;
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