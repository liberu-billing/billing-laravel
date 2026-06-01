<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\AuditLogs;

use App\Models\AuditLog;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    #[\Override]
    protected static ?string $model = AuditLog::class;

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-list';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    #[\Override]
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
