<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecurringBillingResource\Pages;
use App\Models\RecurringBillingConfiguration;
use Filament\Forms;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;

class RecurringBillingResource extends Resource
{
    protected static ?string $model = RecurringBillingConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-refresh';
    protected static ?string $navigationLabel = 'Recurring Billing';
    protected static ?string $navigationGroup = 'Billing';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('frequency')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('billing_day')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->helperText('Day of the month when billing should occur'),
                Forms\Components\DatePicker::make('next_billing_date')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('frequency')
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_day')
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_billing_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('frequency')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ]),
                Filter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            // 'index' => Pages\ListRecurringBillings::route('/'),
            // 'create' => Pages\CreateRecurringBilling::route('/create'),
            // 'edit' => Pages\EditRecurringBilling::route('/{record}/edit'),
        ];
    }    
}