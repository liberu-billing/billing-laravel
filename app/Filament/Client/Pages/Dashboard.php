<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Http\Livewire\Dashboard as DashboardComponent;

class Dashboard extends BaseDashboard
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardComponent::class
        ];
    }

    protected function getColumns(): int|array
    {
        return 2;
    }
}