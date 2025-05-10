<?php

namespace App\Filament\App\Resources\RefundResource\Pages;

use App\Filament\App\Resources\RefundResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRefund extends EditRecord
{
    protected static string $resource = RefundResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}