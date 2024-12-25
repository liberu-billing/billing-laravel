

<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    public $activeCharts = [];
    public $chartPreferences;
    
    protected $availableCharts = [
        'revenue' => [
            'title' => 'Revenue Overview',
            'type' => 'line'
        ],
        'invoices' => [
            'title' => 'Invoice Status',
            'type' => 'pie'
        ],
        'clients' => [
            'title' => 'Active Clients',
            'type' => 'bar'
        ]
    ];

    public function mount()
    {
        $this->chartPreferences = auth()->user()->dashboard_preferences ?? array_fill_keys(array_keys($this->availableCharts), true);
        $this->activeCharts = array_keys(array_filter($this->chartPreferences));
    }

    public function toggleChart($chartKey)
    {
        $this->chartPreferences[$chartKey] = !$this->chartPreferences[$chartKey];
        auth()->user()->update(['dashboard_preferences' => $this->chartPreferences]);
        $this->activeCharts = array_keys(array_filter($this->chartPreferences));
    }

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
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $revenue->pluck('total')
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
            'series' => $statuses->pluck('count')
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
            'series' => [
                [
                    'name' => 'New Clients',
                    'data' => $clients->pluck('count')
                ]
            ]
        ];
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'metrics' => $this->getMetrics(),
            'availableCharts' => $this->availableCharts
        ]);
    }
}