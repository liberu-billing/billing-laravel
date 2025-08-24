<?php

namespace App\Filament\App\Resources\Affiliates\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Affiliates\AffiliateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliate extends EditRecord
{
    protected static string $resource = AffiliateResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}