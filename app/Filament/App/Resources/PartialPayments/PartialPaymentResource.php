<?php

namespace App\Filament\App\Resources\PartialPayments;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\PartialPayments\Pages\ListPartialPayments;
use App\Filament\App\Resources\PartialPayments\Pages\CreatePartialPayment;
use App\Filament\App\Resources\PartialPayments\Pages\EditPartialPayment;
use App\Filament\App\Resources\PartialPaymentResource\Pages;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\PartialPaymentService;

class PartialPaymentResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(Invoice::where('status', 'pending')->orWhere('status', 'partially_paid')->pluck('invoice_number', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->label('Payment Amount'),
                Select::make('payment_gateway_id')
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
                TextColumn::make('invoice_number')->sortable(),
                TextColumn::make('total_amount')->sortable(),
                TextColumn::make('paid_amount')->sortable(),
                TextColumn::make('remaining_amount')->sortable(),
                TextColumn::make('status')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListPartialPayments::route('/'),
            'create' => CreatePartialPayment::route('/create'),
            'edit' => EditPartialPayment::route('/{record}/edit'),
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
