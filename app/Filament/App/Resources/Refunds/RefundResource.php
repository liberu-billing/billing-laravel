<?php

namespace App\Filament\App\Resources\Refunds;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Exception;
use App\Filament\App\Resources\Refunds\Pages\ListRefunds;
use App\Filament\App\Resources\Refunds\Pages\CreateRefund;
use App\Filament\App\Resources\Refunds\Pages\EditRefund;
use App\Filament\App\Resources\RefundResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\RefundService;
use Filament\Notifications\Notification;
use Closure;

class RefundResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'Refunds';

    protected static ?string $modelLabel = 'Refund';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('payment_id')
                    ->label('Payment')
                    ->options(function () {
                        return Payment::whereIn('refund_status', ['none', 'partial'])
                            ->with('invoice')
                            ->get()
                            ->mapWithKeys(function ($payment) {
                                return [
                                    $payment->id => "Payment #{$payment->id} - Invoice #{$payment->invoice->invoice_number} ({$payment->amount} {$payment->currency})"
                                ];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $payment = Payment::find($state);
                            $set('max_refund_amount', $payment->getRemainingRefundableAmount());
                            $set('currency', $payment->currency);
                        }
                    }),
                
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->label('Refund Amount')
                    ->hint(fn ($state, $record) => $record ? "Maximum refundable amount: {$record->getRemainingRefundableAmount()} {$record->currency}" : '')
                    ->rules([
                        'required',
                        'numeric',
                        'min:0.01',
                        function (string $attribute, $value, Closure $fail) {
                            $payment = Payment::find(request()->input('data.payment_id'));
                            if ($payment && $value > $payment->getRemainingRefundableAmount()) {
                                $fail("The refund amount cannot exceed the remaining refundable amount of {$payment->getRemainingRefundableAmount()} {$payment->currency}");
                            }
                        },
                    ]),
                
                Textarea::make('reason')
                    ->label('Refund Reason')
                    ->required()
                    ->maxLength(1000),
                    
                Hidden::make('currency'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Payment ID')
                    ->sortable(),
                TextColumn::make('invoice.invoice_number')
                    ->label('Invoice Number')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Original Amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                TextColumn::make('refunded_amount')
                    ->label('Refunded Amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                BadgeColumn::make('refund_status')
                    ->colors([
                        'danger' => 'none',
                        'warning' => 'partial',
                        'success' => 'full',
                    ]),
                TextColumn::make('created_at')
                    ->label('Payment Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('refund_status')
                    ->options([
                        'none' => 'No Refund',
                        'partial' => 'Partially Refunded',
                        'full' => 'Fully Refunded',
                    ]),
            ])
            ->recordActions([
                Action::make('refund')
                    ->visible(fn (Payment $record) => $record->isRefundable())
                    ->schema([
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->label('Refund Amount')
                            ->rules([
                                'required',
                                'numeric',
                                'min:0.01',
                                function (string $attribute, $value, Closure $fail) use ($record) {
                                    if ($value > $record->getRemainingRefundableAmount()) {
                                        $fail("Cannot refund more than {$record->getRemainingRefundableAmount()} {$record->currency}");
                                    }
                                },
                            ]),
                        Textarea::make('reason')
                            ->required()
                            ->label('Refund Reason'),
                    ])
                    ->action(function (array $data, Payment $record) {
                        $refundService = app(RefundService::class);
                        
                        try {
                            $result = $refundService->processRefund($record, $data['amount']);
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title('Refund processed successfully')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Refund failed')
                                    ->body($result['message'])
                                    ->danger()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Error processing refund')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => ListRefunds::route('/'),
            'create' => CreateRefund::route('/create'),
            'edit' => EditRefund::route('/{record}/edit'),
        ];
    }    

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['invoice', 'paymentGateway'])
            ->latest();
    }
}
