<?php

namespace App\Filament\Resources\PaymentPlans;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\PaymentPlanResource\Pages;
use App\Models\PaymentPlan;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class PaymentPlanResource extends Resource
{
    protected static ?string $model = PaymentPlan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->required(),
                TextInput::make('total_installments')
                    ->required()
                    ->numeric()
                    ->minValue(2),
                Select::make('frequency')
                    ->options([
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                    ])
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number'),
                TextColumn::make('total_installments'),
                TextColumn::make('installment_amount'),
                TextColumn::make('frequency'),
                TextColumn::make('status'),
            ])
            ->filters([
                //
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
            // 'index' => Pages\ListPaymentPlans::route('/'),
            // 'create' => Pages\CreatePaymentPlan::route('/create'),
            // 'edit' => Pages\EditPaymentPlan::route('/{record}/edit'),
        ];
    }
}