<?php

namespace App\Filament\Pages;

use Exception;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Models\SubscriptionPlan;
use App\Services\BillingService;

class SubscriptionPlansPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected string $view = 'filament.pages.subscription-plans';

    public $selectedPlan;
    public $billingCycle = 'monthly';
    public $plans;

    public function mount(): void
    {
        $this->plans = SubscriptionPlan::where('is_active', true)->get();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('selectedPlan')
                            ->label('Select Plan')
                            ->options($this->plans?->pluck('name', 'id') ?? [])
                            ->required(),
                        Select::make('billingCycle')
                            ->label('Billing Cycle')
                            ->options([
                                'monthly'       => 'Monthly',
                                'quarterly'     => 'Quarterly',
                                'semi-annually' => 'Semi-annually',
                                'annually'      => 'Annually',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public function subscribe(): mixed
    {
        $plan = SubscriptionPlan::findOrFail($this->selectedPlan);

        try {
            $billingService = app(BillingService::class);

            $subscription = $billingService->createSubscription(
                auth()->user()->customer,
                $plan,
                $this->billingCycle
            );

            return redirect()->route('filament.pages.checkout', [
                'subscription' => $subscription->id,
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title('Error creating subscription')
                ->danger()
                ->send();

            return null;
        }
    }
}
