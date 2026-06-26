<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Fillable([
    'name',
    'hostname',
    'control_panel',
    'api_token',
    'api_url',
    'username',
    'ip_address',
    'is_active',
    'max_accounts',
    'active_accounts',
])]
class HostingServer extends Model
{
    use HasFactory;
    use HasTeam;

    #[Override]
    protected function casts(): array
    {

        return [
            'is_active' => 'boolean',
            'max_accounts' => 'integer',
            'active_accounts' => 'integer',
        ];

    }

    public function hostingAccounts(): HasMany
    {
        return $this->hasMany(HostingAccount::class);
    }

    public function isAtCapacity(): bool
    {
        return $this->max_accounts > 0 && $this->active_accounts >= $this->max_accounts;
    }

    public function hasCapacity(): bool
    {
        return !$this->isAtCapacity();
    }

    public function getUsagePercentage(): float|int
    {
        if ($this->max_accounts === 0) {
            return 0;
        }

        return ($this->active_accounts / $this->max_accounts) * 100;
    }
}
