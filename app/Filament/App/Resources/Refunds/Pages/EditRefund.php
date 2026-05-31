<?php

namespace App\Filament\App\Resources\Refunds\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Refunds\RefundResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRefund extends EditRecord
{
    #[\Override]
    protected static string $resource = RefundResource::class;

    #[\Override]
    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}