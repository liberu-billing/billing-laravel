<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Models\Subscription;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;

class ManageSubscriptionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static string $view = 'filament.pages.manage-subscription';
    public $subscription;
    public $card_number;
    public $expiry_month;
    public $expiry_year;
    public $cvv;

    public function mount()
    {
        $this->subscription = Auth::user()->subscription;
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('card_number')
                ->label('Card Number')
                ->required()
                ->maxLength(16),
            Select::make('expiry_month')
                ->label('Expiry Month')
                ->options(array_combine(range(1, 12), range(1, 12)))
                ->required(),
            Select::make('expiry_year')
                ->label('Expiry Year')
                ->options(array_combine(range(date('Y'), date('Y') + 10), range(date('Y'), date('Y') + 10)))
                ->required(),
            TextInput::make('cvv')
                ->label('CVV')
                ->required()
                ->maxLength(4),
        ];
    }

    public function updatePaymentMethod()
    {
        $this->validate();

        // Here you would typically interact with your payment gateway to update the payment method
        // For this example, we'll just create a new PaymentMethod record
        PaymentMethod::create([
            'user_id' => Auth::id(),
            'card_last_four' => substr($this->card_number, -4),
            'card_expiration' => $this->expiry_month . '/' . $this->expiry_year,
            // Don't store the full card number or CVV for security reasons
        ]);

        Notification::make()
            ->title('Payment method updated successfully')
            ->success()
            ->send();
    }

    public function cancelSubscription()
    {
        $this->subscription->cancel();

        Notification::make()
            ->title('Subscription cancelled successfully')
            ->success()
            ->send();
    }

    public function resumeSubscription()
    {
        $this->subscription->resume();

        Notification::make()
            ->title('Subscription resumed successfully')
            ->success()
            ->send();
    }
}