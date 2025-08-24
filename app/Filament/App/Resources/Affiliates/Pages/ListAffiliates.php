<?php

namespace App\Filament\App\Resources\AffiliateResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\AffiliateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliates extends ListRecords
{
    protected static string $resource = AffiliateResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}