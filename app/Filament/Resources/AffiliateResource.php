<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Models\Affiliate;
use App\Services\AffiliateReportingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('commission_rate')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->min(0)
                    ->max(100),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('total_earnings')
                    ->disabled()
                    ->label('Total Earnings')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('commission_rate'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('total_earnings')
                    ->money('usd')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('generateReport')
                    ->label('Generate Report')
                    ->icon('heroicon-o-document-report')
                    ->action(function (Affiliate $record, AffiliateReportingService $reportingService) {
                        $report = $reportingService->generateReport($record, now()->subMonth(), now());
                        // Here you can return the report data or redirect to a report view
                        return redirect()->route('affiliate.report', $report);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('commission_rate'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('total_earnings')
                    ->money('usd')
                    ->sortable(),
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
            'index' => Pages\ListAffiliates::route('/'),
            'create' => Pages\CreateAffiliate::route('/create'),
            'edit' => Pages\EditAffiliate::route('/{record}/edit'),
        ];
    }
}
