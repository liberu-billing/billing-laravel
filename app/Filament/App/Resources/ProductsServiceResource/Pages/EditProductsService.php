<?php

namespace App\Filament\App\Resources\ProductsServiceResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\ProductsServiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductsService extends EditRecord
{
    protected static string $resource = ProductsServiceResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}