<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Teams\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                [
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    // User has an admin-panel tenancy global scope (members of the current team only);
                    // an owner can be any user, so bypass it for both options and existence validation.
                    Select::make('user_id')
                        ->label('Owner')
                        ->options(fn (): array => User::withoutGlobalScopes()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required()
                        ->rule('exists:users,id'),
                    Toggle::make('personal_team'),
                    Toggle::make('is_default_for_registration')
                        ->helperText('New users are added to this team on registration'),
                ]
            );
    }
}
