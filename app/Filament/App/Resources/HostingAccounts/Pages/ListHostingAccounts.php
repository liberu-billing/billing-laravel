<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\HostingAccounts\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\HostingAccounts\HostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostingAccounts extends ListRecords
{
    #[\Override]
    protected static string $resource = HostingAccountResource::class;

    #[\Override]
    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
