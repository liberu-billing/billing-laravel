<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Users;

use App\Filament\App\Resources\Users\Pages\CreateUser;
use App\Filament\App\Resources\Users\Pages\EditUser;
use App\Filament\App\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

class UserResource extends Resource
{
    #[Override]
    protected static ?string $model = User::class;

    // User belongs to teams many-to-many (Jetstream HasTeams::teams), not the
    // singular `team` belongsTo that the panel's ownershipRelationship assumes.
    protected static ?string $tenantOwnershipRelationshipName = 'teams';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(
                [
                    //
                ]
            );
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns(
                [
                    //
                ]
            )
            ->filters(
                [
                    //
                ]
            )
            ->recordActions(
                [
                    EditAction::make(),
                ]
            )
            ->toolbarActions(
                [
                    BulkActionGroup::make(
                        [
                            DeleteBulkAction::make(),
                        ]
                    ),
                ]
            );
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
