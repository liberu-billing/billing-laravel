<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
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
])]
class HostingAccount extends Model
{
    use HasFactory;
    use HasTeam;

    #[Override]
    protected function casts(): array
    {

        return [
            'addons' => 'array',
            'price' => 'decimal:2',
        ];

    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(
            HostingServer::class,
            'hosting_server_id'
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasDomain(): bool
    {
        return !empty($this->domain);
    }

    public function hasAddon($addon): bool
    {
        $addons = $this->addons ?? [];

        return in_array(
            $addon,
            $addons,
            true
        );
    }

    public function getAddons()
    {
        return $this->addons ?? [];
    }
}
