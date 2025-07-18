<?php

namespace App\Filament\App\Resources\ProductsServiceResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\ProductsServiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductsServices extends ListRecords
{
    protected static string $resource = ProductsServiceResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}