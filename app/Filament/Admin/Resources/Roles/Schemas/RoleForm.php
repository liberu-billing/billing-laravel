<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        
                        TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('The name of the role (e.g., editor, manager)'),
                        Select::make('permissions')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Select the permissions for this role')
                            ->required(),
            ])
        ]);
    }
}

    