<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use App\Models\Subscription;
use App\Models\Products_Service;
use Illuminate\Support\Facades\Auth;

class ManageSubscriptionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static string $view = 'filament.pages.manage-subscription';
    
    public $subscription;
    public $selectedProduct;
    public $renewalPeriod;
    public $autoRenew;
    public $startDate;

    public function mount()
    {
        $this->subscription = Auth::user()->subscription;
        if ($this->subscription) {
            $this->selectedProduct = $this->subscription->product_service_id;
            $this->renewalPeriod = $this->subscription->renewal_period;
            $this->autoRenew = $this->subscription->auto_renew;
            $this->startDate = $this->subscription->start_date;
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('selectedProduct')
                                ->label('Service')
                                ->options(Products_Service::pluck('name', 'id'))
                                ->required(),
                            
                            Select::make('renewalPeriod')
                                ->label('Billing Cycle')
                                ->options([
                                    'monthly' => 'Monthly',
                                    'quarterly' => 'Quarterly',
                                    'semi-annually' => 'Semi-annually',
                                    'annually' => 'Annually',
                                ])
                                ->required(),
                            
                            DatePicker::make('startDate')
                                ->label('Start Date')
                                ->required(),
                            
                            Toggle::make('autoRenew')
                                ->label('Auto Renew')
                                ->default(true),
                        ]),
                ]),
        ];
    }

    public function save()
    {
        $data = $this->validate();
        
        $product = Products_Service::findOrFail($this->selectedProduct);
        
        if (!$this->subscription) {
            $this->subscription = new Subscription();
        }
        
        $this->subscription->fill([
            'customer_id' => Auth::user()->customer->id,
            'product_service_id' => $this->selectedProduct,
            'start_date' => $this->startDate,
            'renewal_period' => $this->renewalPeriod,
            'auto_renew' => $this->autoRenew,
            'price' => $product->price,
            'currency' => $product->currency,
            'status' => 'active',
        ]);
        
        $this->subscription->save();
        
        Notification::make()
            ->title('Subscription updated successfully')
            ->success()
            ->send();
    }

    public function cancel()
    {
        $this->subscription?->cancel();
        
        Notification::make()
            ->title('Subscription cancelled successfully')
            ->success()
            ->send();
    }

    public function resume()
    {
        $this->subscription?->resume();
        
        Notification::make()
            ->title('Subscription resumed successfully')
            ->success()
            ->send();
    }
}