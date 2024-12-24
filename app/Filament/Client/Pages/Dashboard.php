

<?php

namespace App\Filament\Client\Pages;

use App\Models\Invoice;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        $client = auth()->user();
        
        return [
            StatsOverviewWidget::class,
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }
}