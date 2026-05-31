<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Affiliates\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Affiliates\AffiliateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliates extends ListRecords
{
    #[\Override]
    protected static string $resource = AffiliateResource::class;

    #[\Override]
    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
