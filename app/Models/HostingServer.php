<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingServer extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_accounts' => 'integer',
        'active_accounts' => 'integer',
    ];

    public function hostingAccounts()
    {
        return $this->hasMany(HostingAccount::class);
    }

    public function isAtCapacity()
    {
        return $this->max_accounts > 0 && $this->active_accounts >= $this->max_accounts;
    }

    public function hasCapacity()
    {
        return !$this->isAtCapacity();
    }

    public function getUsagePercentage()
    {
        if ($this->max_accounts === 0) {
            return 0;
        }
        return ($this->active_accounts / $this->max_accounts) * 100;
    }
}
