<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-refresh';
    protected static ?string $navigationLabel = 'Recurring Billing';
    protected static string | \UnitEnum | null $navigationGroup = 'Billing';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->required()
                    ->searchable(),
                Select::make('frequency')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ])
                    ->required(),
                TextInput::make('billing_day')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->helperText('Day of the month when billing should occur'),
                DatePicker::make('next_billing_date')
                    ->required(),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('frequency')
                    ->sortable(),
                TextColumn::make('billing_day')
                    ->sortable(),
                TextColumn::make('next_billing_date')
                    ->date()
                    ->sortable(),
                BooleanColumn::make('is_active')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('frequency')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ]),
                Filter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
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