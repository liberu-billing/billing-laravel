<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'api_key',
    'secret_key',
    'is_active',
])]
class PaymentGateway extends Model
{
    use HasTeam;

    #[\Override]
    protected function casts(): array
    {

        return [
            'is_active' => 'boolean',
        ];

    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
