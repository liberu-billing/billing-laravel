<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Affiliates\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Affiliates\AffiliateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliate extends EditRecord
{
    #[\Override]
    protected static string $resource = AffiliateResource::class;

    #[\Override]
    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
