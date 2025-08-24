<?php

namespace App\Filament\App\Resources\Affiliates\Pages;

use App\Filament\App\Resources\Affiliates\AffiliateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAffiliate extends CreateRecord
{
    protected static string $resource = AffiliateResource::class;
}