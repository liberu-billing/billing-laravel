<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Override;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $dashboard_preferences
 * @property-read string $profile_photo_url
 * @property-read Team|null $latestTeam
 * @property-read Affiliate|null $affiliate
 * @property-read Affiliate|null $referrer
 * @property-read Collection<int, Integration> $integrations
 * @property-read Collection<int, SavedSearch> $savedSearches
 * @property-read Customer|null $customer
 * @property-read Subscription|null $subscription
 * @property-read Collection<int, Ticket> $tickets
 * @property-read Collection<int, TeamInvitation> $invitations
 * @property-read Collection<int, ConnectedAccount> $connectedAccounts
 */
#[Appends([
    'profile_photo_url',
])]
#[Fillable([
    'name',
    'email',
    'password',
    'referred_by',
])]
#[Hidden([
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
])]
class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants
{
    use HasApiTokens;
    use HasConnectedAccounts;
    use HasFactory;
    use HasPanelShield;
    use HasProfilePhoto {
        HasProfilePhoto::profilePhotoUrl as getPhotoUrl;
    }
    use HasRoles, HasTeams {
        HasTeams::teams insteadof HasRoles;
    }
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use TwoFactorAuthenticatable;

    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dashboard_preferences' => 'array',
        ];
    }

    protected function profilePhotoUrl(): Attribute
    {
        return filter_var(
            $this->profile_photo_path,
            FILTER_VALIDATE_URL
        )
            ? Attribute::get(fn () => $this->profile_photo_path)
            : $this->getPhotoUrl();
    }

    /** @return array<Model>|Collection */
    public function getTenants(Panel $panel): array|Collection
    {
        return $this->teams;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        // Security: without this check any user could load any team's tenant
        // URL and Filament would scope queries to it, exposing other teams' data.
        return $this->belongsToTeam($tenant);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->latestTeam;
    }

    public function latestTeam(): BelongsTo
    {
        return $this->belongsTo(
            Team::class,
            'current_team_id'
        );
    }

    public function affiliate(): HasOne
    {
        return $this->hasOne(Affiliate::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(
            Affiliate::class,
            'referred_by'
        );
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function hasIntegration(string $provider): bool
    {
        return $this->integrations()->where(
            'provider',
            $provider
        )->exists();
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function subscription(): HasOneThrough
    {
        return $this->hasOneThrough(Subscription::class, Customer::class, 'user_id', 'customer_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }
}
