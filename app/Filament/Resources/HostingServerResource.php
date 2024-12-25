

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HostingServerResource\Pages;
use App\Models\HostingServer;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class HostingServerResource extends Resource
{
    protected static ?string $model = HostingServer::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationGroup = 'Hosting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hostname')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('control_panel')
                            ->options([
                                'cpanel' => 'cPanel',
                                'plesk' => 'Plesk',
                                'directadmin' => 'DirectAdmin',
                                'virtualmin' => 'Virtualmin',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('api_token')
                            ->required()
                            ->password()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('api_url')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\TextInput::make('max_accounts')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hostname')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('control_panel')
                    ->colors([
                        'primary' => 'cpanel',
                        'success' => 'plesk',
                        'warning' => 'directadmin',
                        'danger' => 'virtualmin',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('active_accounts')
                    ->label('Active/Max Accounts')
                    ->formatStateUsing(fn ($record) => "{$record->active_accounts}/{$record->max_accounts}"),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('control_panel')
                    ->options([
                        'cpanel' => 'cPanel',
                        'plesk' => 'Plesk',
                        'directadmin' => 'DirectAdmin',
                        'virtualmin' => 'Virtualmin',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostingServers::route('/'),
            'create' => Pages\CreateHostingServer::route('/create'),
            'edit' => Pages\EditHostingServer::route('/{record}/edit'),
        ];
    }
}