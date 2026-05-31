<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Users\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    #[\Override]
    protected static string $resource = UserResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
