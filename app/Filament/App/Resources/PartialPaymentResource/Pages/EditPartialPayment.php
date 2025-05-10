<?php

namespace App\Filament\App\Resources\PartialPaymentResource\Pages;

use App\Filament\App\Resources\PartialPaymentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartialPayment extends EditRecord
{
    protected static string $resource = PartialPaymentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}