<?php

namespace App\Filament\App\Resources\RefundResource\Pages;

use App\Filament\App\Resources\RefundResource;
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