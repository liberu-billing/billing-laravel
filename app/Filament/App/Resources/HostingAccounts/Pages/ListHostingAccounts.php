<?php

namespace App\Filament\App\Resources\HostingAccounts\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\HostingAccounts\HostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostingAccounts extends ListRecords
{
    protected static string $resource = HostingAccountResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}