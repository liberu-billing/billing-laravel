<?php

namespace App\Filament\Resources\Subscriptions;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-collection';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('subscription_plan_id')
                    ->relationship('subscriptionPlan', 'name')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                        'suspended' => 'Suspended'
                    ])
                    ->required(),
                Toggle::make('auto_renew')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name'),
                TextColumn::make('subscriptionPlan.name'),
                TextColumn::make('status'),
                TextColumn::make('start_date'),
                TextColumn::make('end_date'),
                BooleanColumn::make('auto_renew'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                        'suspended' => 'Suspended'
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            // 'index' => Pages\ListSubscriptions::route('/'),
            // 'create' => Pages\CreateSubscription::route('/create'),
            // 'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }    
}