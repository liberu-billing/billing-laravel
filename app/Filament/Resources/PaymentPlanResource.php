

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentPlanResource\Pages;
use App\Models\PaymentPlan;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class PaymentPlanResource extends Resource
{
    protected static ?string $model = PaymentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->required(),
                Forms\Components\TextInput::make('total_installments')
                    ->required()
                    ->numeric()
                    ->minValue(2),
                Forms\Components\Select::make('frequency')
                    ->options([
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_number'),
                Tables\Columns\TextColumn::make('total_installments'),
                Tables\Columns\TextColumn::make('installment_amount'),
                Tables\Columns\TextColumn::make('frequency'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPaymentPlans::route('/'),
            'create' => Pages\CreatePaymentPlan::route('/create'),
            'edit' => Pages\EditPaymentPlan::route('/{record}/edit'),
        ];
    }
}