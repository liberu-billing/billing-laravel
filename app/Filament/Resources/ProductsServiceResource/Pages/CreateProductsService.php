<?php

namespace App\Filament\Resources\ProductsServiceResource\Pages;

use App\Filament\Resources\ProductsServiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductsService extends CreateRecord
{
    protected static string $resource = ProductsServiceResource::class;
}