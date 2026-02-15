<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingAccount extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'customer_id',
        'subscription_id',
        'hosting_server_id',
        'control_panel',
        'username',
        'domain',
        'package',
        'status',
        'price',
        'addons',
    ];

    protected $casts = [
        'addons' => 'array',
        'price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'hosting_server_id');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function hasDomain()
    {
        return !empty($this->domain);
    }

    public function hasAddon($addon)
    {
        $addons = $this->addons ?? [];
        return in_array($addon, $addons);
    }

    public function getAddons()
    {
        return $this->addons ?? [];
    }
}