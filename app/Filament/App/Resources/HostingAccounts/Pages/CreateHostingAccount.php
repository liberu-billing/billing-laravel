<?php

namespace App\Filament\App\Resources\HostingAccounts\Pages;

use App\Filament\App\Resources\HostingAccounts\HostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHostingAccount extends CreateRecord
{
    protected static string $resource = HostingAccountResource::class;
}