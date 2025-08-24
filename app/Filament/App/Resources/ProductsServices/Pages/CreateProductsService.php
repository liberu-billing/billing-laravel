<?php

namespace App\Filament\App\Resources\ProductsServices\Pages;

use App\Filament\App\Resources\ProductsServices\ProductsServiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductsService extends CreateRecord
{
    protected static string $resource = ProductsServiceResource::class;
}