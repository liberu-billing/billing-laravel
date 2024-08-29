<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PartialPaymentResource\Pages;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\PartialPaymentService;

class PartialPaymentResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(Invoice::where('status', 'pending')->orWhere('status', 'partially_paid')->pluck('invoice_number', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->label('Payment Amount'),
                Forms\Components\Select::make('payment_gateway_id')
                    ->label('Payment Gateway')
                    ->options(PaymentGateway::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')->sortable(),
                Tables\Columns\TextColumn::make('remaining_amount')->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPartialPayments::route('/'),
            'create' => Pages\CreatePartialPayment::route('/create'),
            'edit' => Pages\EditPartialPayment::route('/{record}/edit'),
        ];
    }    

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->where('status', 'pending')
                      ->orWhere('status', 'partially_paid');
            })
            ->latest();
    }
}
