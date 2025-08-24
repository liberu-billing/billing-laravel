<?php

namespace App\Filament\App\Resources\UserResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
