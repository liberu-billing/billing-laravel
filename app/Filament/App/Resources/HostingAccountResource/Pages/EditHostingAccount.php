<?php

namespace App\Filament\App\Resources\HostingAccountResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\HostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostingAccount extends EditRecord
{
    protected static string $resource = HostingAccountResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}