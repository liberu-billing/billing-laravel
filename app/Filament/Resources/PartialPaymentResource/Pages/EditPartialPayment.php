<?php

namespace App\Filament\Resources\PartialPaymentResource\Pages;

use App\Filament\Resources\PartialPaymentResource;
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