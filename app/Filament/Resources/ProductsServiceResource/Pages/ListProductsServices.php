<?php

namespace App\Filament\Resources\ProductsServiceResource\Pages;

use App\Filament\Resources\ProductsServiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductsServices extends ListRecords
{
    protected static string $resource = ProductsServiceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}