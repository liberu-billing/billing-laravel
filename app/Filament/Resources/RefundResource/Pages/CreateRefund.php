<?php

namespace App\Filament\Resources\RefundResource\Pages;

use App\Filament\Resources\RefundResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRefund extends CreateRecord
{
    protected static string $resource = RefundResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}