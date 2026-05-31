<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\HostingAccounts\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\HostingAccounts\HostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostingAccount extends EditRecord
{
    #[\Override]
    protected static string $resource = HostingAccountResource::class;

    #[\Override]
    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
