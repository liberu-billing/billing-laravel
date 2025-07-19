<?php

namespace App\Filament\App\Resources\HostingAccountResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\HostingAccountResource;
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