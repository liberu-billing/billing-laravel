

<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Component;
use Filament\Forms;

class PaymentForm extends Component
{
    public static function make(): Forms\Components\Card
    {
        return Forms\Components\Card::make()
            ->schema([
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'stripe' => 'Credit Card (Stripe)',
                        'paypal' => 'PayPal',
                    ])
                    ->required()
                    ->reactive(),
                
                Forms\Components\TextInput::make('card_number')
                    ->label('Card Number')
                    ->numeric()
                    ->maxLength(16)
                    ->hidden(fn ($get) => $get('payment_method') !== 'stripe'),
                
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('expiry_month')
                            ->label('Month')
                            ->numeric()
                            ->maxLength(2),
                        Forms\Components\TextInput::make('expiry_year')
                            ->label('Year')
                            ->numeric()
                            ->maxLength(4),
                        Forms\Components\TextInput::make('cvv')
                            ->label('CVV')
                            ->numeric()
                            ->maxLength(4),
                    ])
                    ->hidden(fn ($get) => $get('payment_method') !== 'stripe'),
            ]);
    }
}