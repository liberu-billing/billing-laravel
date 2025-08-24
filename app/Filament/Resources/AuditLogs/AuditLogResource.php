<?php
namespace App\Filament\Resources;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-list';
    protected static string | \UnitEnum | null $navigationGroup = 'Administration';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('event')
                    ->searchable(),
                TextColumn::make('auditable_type')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('event'),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}