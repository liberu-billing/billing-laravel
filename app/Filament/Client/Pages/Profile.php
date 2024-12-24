

<?php

namespace App\Filament\Client\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.client.pages.profile';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'company' => auth()->user()->company,
            'phone' => auth()->user()->phone,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('clients', 'email', ignorable: auth()->user()),
                TextInput::make('company')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('current_password')
                    ->password()
                    ->label('Current Password')
                    ->required()
                    ->visible(fn ($get) => (bool) $get('password')),
                TextInput::make('password')
                    ->password()
                    ->label('New Password')
                    ->rule(Password::defaults()),
                TextInput::make('password_confirmation')
                    ->password()
                    ->label('Confirm Password')
                    ->visible(fn ($get) => (bool) $get('password'))
                    ->same('password'),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        
        $user = auth()->user();
        
        if (isset($data['password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                $this->addError('current_password', 'The provided password is incorrect.');
                return;
            }
            $data['password'] = Hash::make($data['password']);
        }
        
        $user->update($data);
        
        $this->notify('success', 'Profile updated successfully');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}