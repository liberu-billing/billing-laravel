

<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Currency;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action as TableAction;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoices';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('invoice_number')
                                    ->disabled()
                                    ->label('Invoice Number'),
                                Forms\Components\DatePicker::make('issue_date')
                                    ->disabled(),
                                Forms\Components\DatePicker::make('due_date')
                                    ->disabled(),
                                Forms\Components\TextInput::make('total_amount')
                                    ->disabled()
                                    ->prefix(fn (Invoice $record) => $record->currency),
                                Forms\Components\TextInput::make('status')
                                    ->disabled(),
                                Forms\Components\TextInput::make('remaining_amount')
                                    ->disabled()
                                    ->prefix(fn (Invoice $record) => $record->currency)
                                    ->visible(fn (Invoice $record) => $record->status === 'partially_paid'),
                            ]),
                    ]),
                
                Card::make()
                    ->visible(fn (Invoice $record) => $record->status !== 'paid')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'paypal' => 'PayPal',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_amount')
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
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money(fn (Invoice $record) => $record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'info' => 'partially_paid',
                    ]),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'partially_paid' => 'Partially Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                TableAction::make('pay')
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn (Invoice $record) => $record->status !== 'paid')
                    ->action(function (Invoice $record, array $data) {
                        $record->processPayment($data['payment_method'], $data['payment_amount']);
                    })
                    ->form([
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'paypal' => 'PayPal',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_amount')
                            ->numeric()
                            ->required()
                            ->rules([
                                fn (Invoice $record) => 'max:' . $record->remaining_amount,
                                'min:1',
                            ]),
                    ]),
                Tables\Actions\Action::make('download_pdf')
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
            'index' => Pages\ListInvoices::class,
            'view' => Pages\ViewInvoice::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('client_id', auth()->id());
    }
}