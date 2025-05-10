<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function getMetrics()
    {
        return Cache::remember('dashboard.metrics', 300, function() {
            return [
                'revenue' => $this->getRevenueData(),
                'invoices' => $this->getInvoiceData(),
                'clients' => $this->getClientData()
            ];
        });
    }

    private function getRevenueData()
    {
        $revenue = Invoice::paid()
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $revenue->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue->pluck('total'),
                    'borderColor' => '#4F46E5'
                ]
            ]
        ];
    }

    private function getInvoiceData()
    {
        $statuses = Invoice::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return [
            'labels' => $statuses->pluck('status'),
            'datasets' => [
                [
                    'data' => $statuses->pluck('count'),
                    'backgroundColor' => ['#4F46E5', '#10B981', '#EF4444']
                ]
            ]
        ];
    }

    private function getClientData()
    {
        $clients = Client::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $clients->pluck('date'),
            'datasets' => [
                [
                    'label' => 'New Clients',
                    'data' => $clients->pluck('count'),
                    'backgroundColor' => '#4F46E5'
                ]
            ]
        ];
    }

    public function savePreferences(Request $request)
    {
        $request->validate([
            'charts' => 'required|array'
        ]);

        auth()->user()->update([
            'dashboard_preferences' => $request->charts
        ]);

        return response()->json(['message' => 'Preferences saved']);
    }

    public function getPreferences()
    {
        return response()->json([
            'charts' => auth()->user()->dashboard_preferences
        ]);
    }
}