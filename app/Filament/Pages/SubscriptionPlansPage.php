<?php

namespace App\Filament\Pages;

use Filament\Schemas\Components\Section;
use Exception;
use Filament\Pages\Page;
use App\Models\SubscriptionPlan;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Services\BillingService;

class SubscriptionPlansPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-collection';
    protected string $view = 'filament.pages.subscription-plans';
    
    public $selectedPlan;
    public $billingCycle = 'monthly';
    
    protected $billingService;
    
    public function __construct(BillingService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    public function mount()
    {
        $this->plans = SubscriptionPlan::where('is_active', true)->get();
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Select::make('selectedPlan')
                        ->label('Select Plan')
                        ->options($this->plans->pluck('name', 'id'))
                        ->required(),
                    Select::make('billingCycle')
                        ->label('Billing Cycle')
                        ->options([
                            'monthly' => 'Monthly',
                            'quarterly' => 'Quarterly',
                            'semi-annually' => 'Semi-annually',
                            'annually' => 'Annually',
                        ])
                        ->required(),
                ]),
        ];
    }

    public function subscribe()
    {
        $plan = SubscriptionPlan::findOrFail($this->selectedPlan);
        
        try {
            $subscription = $this->billingService->createSubscription(
                auth()->user()->customer,
                $plan,
                $this->billingCycle
            );

            return redirect()->route('filament.pages.checkout', [
                'subscription' => $subscription->id
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title('Error creating subscription')
                ->danger()
                ->send();
        }
    }
}