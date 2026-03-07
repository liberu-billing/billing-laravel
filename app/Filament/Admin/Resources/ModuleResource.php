<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ModuleResource\Pages\ListModules;
use App\Filament\Admin\Resources\ModuleResource\Pages\ViewModule;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ModuleResource extends Resource
{
    protected static ?string $model = null;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string | \UnitEnum | null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Modules';

    protected static ?string $modelLabel = 'Module';

    protected static ?string $pluralModelLabel = 'Modules';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Module Name')
                    ->searchable(),
                TextColumn::make('version')
                    ->label('Version'),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                IconColumn::make('enabled')
                    ->boolean()
                    ->label('Enabled'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModules::route('/'),
            'view'  => ViewModule::route('/{record}'),
        ];
    }
}
