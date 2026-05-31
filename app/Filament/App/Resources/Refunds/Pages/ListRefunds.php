<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Refunds\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Refunds\RefundResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRefunds extends ListRecords
{
    #[\Override]
    protected static string $resource = RefundResource::class;

    #[\Override]
    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
