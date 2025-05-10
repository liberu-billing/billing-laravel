<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ProductsServiceResource\Pages;
use App\Models\Products_Service;
use App\Models\Tld;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsServiceResource extends Resource
{
    protected static ?string $model = Products_Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('base_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'hosting' => 'Hosting',
                        'domain' => 'Domain',
                        'addon' => 'Add-on',
                    ])
                    ->reactive(),
                Forms\Components\Select::make('pricing_model')
                    ->required()
                    ->options([
                        'fixed' => 'Fixed',
                        'tiered' => 'Tiered',
                        'usage_based' => 'Usage-based',
                    ])
                    ->reactive(),
                Forms\Components\KeyValue::make('custom_pricing_data')
                    ->keyLabel('Tier/Usage')
                    ->valueLabel('Price')
                    ->visible(fn (Forms\Get $get) => in_array($get('pricing_model'), ['tiered', 'usage_based']))
                    ->columnSpanFull(),
                Forms\Components\Select::make('tld_id')
                    ->label('TLD')
                    ->options(Tld::all()->pluck('name', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('type') === 'domain')
                    ->required(fn (Forms\Get $get) => $get('type') === 'domain'),
                Forms\Components\Select::make('markup_type')
                    ->label('Markup Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'domain')
                    ->required(fn (Forms\Get $get) => $get('type') === 'domain'),
                Forms\Components\TextInput::make('markup_value')
                    ->label('Markup Value')
                    ->numeric()
                    ->visible(fn (Forms\Get $get) => $get('type') === 'domain')
                    ->required(fn (Forms\Get $get) => $get('type') === 'domain'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('base_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pricing_model'),
                Tables\Columns\TextColumn::make('tld.name')
                    ->label('TLD')
                    ->visible(fn ($record) => $record->type === 'domain'),
                Tables\Columns\TextColumn::make('markup_type')
                    ->visible(fn ($record) => $record->type === 'domain'),
                Tables\Columns\TextColumn::make('markup_value')
                    ->visible(fn ($record) => $record->type === 'domain'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProductsServices::route('/'),
            'create' => Pages\CreateProductsService::route('/create'),
            'edit' => Pages\EditProductsService::route('/{record}/edit'),
        ];
    }
}