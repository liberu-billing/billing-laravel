<?php

namespace App\Filament\App\Resources\AffiliateResource\Pages;

use App\Filament\App\Resources\AffiliateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliate extends EditRecord
{
    protected static string $resource = AffiliateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}