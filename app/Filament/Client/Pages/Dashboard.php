<?php

declare(strict_types=1);

namespace App\Filament\Client\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    #[\Override]
    protected string $view = 'filament.pages.dashboard';
}
