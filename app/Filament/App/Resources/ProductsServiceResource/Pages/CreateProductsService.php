<?php

namespace App\Filament\App\Resources\ProductsServiceResource\Pages;

use App\Filament\App\Resources\ProductsServiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductsService extends CreateRecord
{
    protected static string $resource = ProductsServiceResource::class;
}