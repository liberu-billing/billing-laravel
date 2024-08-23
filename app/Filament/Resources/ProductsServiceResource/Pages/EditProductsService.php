<?php

namespace App\Filament\Resources\ProductsServiceResource\Pages;

use App\Filament\Resources\ProductsServiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductsService extends EditRecord
{
    protected static string $resource = ProductsServiceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}