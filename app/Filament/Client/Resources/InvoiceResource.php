<?php

namespace App\Filament\Client\Resources;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use App\Filament\Client\Resources\InvoiceResource\Pages\ListInvoices;
use App\Filament\Client\Resources\InvoiceResource\Pages\ViewInvoice;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Client\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Currency;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Actions\Action;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoices';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->disabled()
                                    ->label('Invoice Number'),
                                DatePicker::make('issue_date')
                                    ->disabled(),
                                DatePicker::make('due_date')
                                    ->disabled(),
                                TextInput::make('total_amount')
                                    ->disabled()
                                    ->prefix(fn (Invoice $record) => $record->currency),
                                TextInput::make('status')
                                    ->disabled(),
                                TextInput::make('remaining_amount')
                                    ->disabled()
                                    ->prefix(fn (Invoice $record) => $record->currency)
                                    ->visible(fn (Invoice $record) => $record->status === 'partially_paid'),
                            ]),
                    ]),
                
                Section::make()
                    ->visible(fn (Invoice $record) => $record->status !== 'paid')
                    ->schema([
                        Select::make('payment_method')
                            ->options([
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'paypal' => 'PayPal',
                            ])
                            ->required(),
                        TextInput::make('payment_amount')
                            ->numeric()
                            ->required()
                            ->rules([
                                fn (Invoice $record) => 'max:' . $record->remaining_amount,
                                'min:1',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money(fn (Invoice $record) => $record->currency)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'info' => 'partially_paid',
                    ]),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'partially_paid' => 'Partially Paid',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('pay')
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn (Invoice $record) => $record->status !== 'paid')
                    ->action(function (Invoice $record, array $data) {
                        $record->processPayment($data['payment_method'], $data['payment_amount']);
                    })
                    ->schema([
                        Select::make('payment_method')
                            ->options([
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'paypal' => 'PayPal',
                            ])
                            ->required(),
                        TextInput::make('payment_amount')
                            ->numeric()
                            ->required()
                            ->rules([
                                fn (Invoice $record) => 'max:' . $record->remaining_amount,
                                'min:1',
                            ]),
                    ]),
                Action::make('download_pdf')
                    ->icon('heroicon-o-document-download')
                    ->action(fn (Invoice $record) => response()->streamDownload(
                        fn () => print($record->generatePdf()),
                        "invoice-{$record->invoice_number}.pdf"
                    )),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::class,
            'view' => ViewInvoice::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('client_id', auth()->id());
    }
}