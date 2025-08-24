<?php

namespace App\Filament\App\Resources\RefundResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\RefundResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRefunds extends ListRecords
{
    protected static string $resource = RefundResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}