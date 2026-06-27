<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                [
                    TextInput::make('code')
                        ->required()
                        ->maxLength(3),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('symbol')
                        ->maxLength(255),
                    TextInput::make('exchange_rate')
                        ->required()
                        ->numeric()
                        ->helperText('Rate relative to the base currency.'),
                    TextInput::make('decimal_precision')
                        ->required()
                        ->integer()
                        ->default(2),
                    Toggle::make('is_enabled')
                        ->default(true),
                    Toggle::make('is_base')
                        ->helperText('Base currency for reports; exchange_rate should be 1.'),
                ]
            );
    }
}
