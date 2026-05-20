<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SiteSettingsResource\Pages\CreateSiteSettings;
use App\Filament\Admin\Resources\SiteSettingsResource\Pages\EditSiteSettings;
use App\Filament\Admin\Resources\SiteSettingsResource\Pages\ListSiteSettings;
use App\Models\SiteSettings;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingsResource extends Resource
{
    protected static ?string $model = SiteSettings::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('currency')
                    ->maxLength(10),
                TextInput::make('default_language')
                    ->maxLength(10)
                    ->default('en'),
                TextInput::make('address')
                    ->maxLength(255),
                TextInput::make('country')
                    ->maxLength(255),
                TextInput::make('phone_01')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('phone_02')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('phone_03')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('facebook')
                    ->url()
                    ->maxLength(255),
                TextInput::make('twitter')
                    ->url()
                    ->maxLength(255),
                TextInput::make('github')
                    ->url()
                    ->maxLength(255),
                TextInput::make('youtube')
                    ->url()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email'),
                TextColumn::make('currency'),
                TextColumn::make('default_language'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSiteSettings::route('/'),
            'create' => CreateSiteSettings::route('/create'),
            'edit'   => EditSiteSettings::route('/{record}/edit'),
        ];
    }
}
