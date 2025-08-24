<?php

namespace App\Filament\App\Resources\Users\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
