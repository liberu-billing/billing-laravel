<?php

namespace App\Filament\Resources\AffiliateResource\Pages;

use App\Filament\Resources\AffiliateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliates extends ListRecords
{
    protected static string $resource = AffiliateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}