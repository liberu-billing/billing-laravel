<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'api_key',
    'secret_key',
    'is_active',
])]
class PaymentGateway extends Model
{
    use HasFactory;
    use HasTeam;

    #[\Override]
    protected function casts(): array
    {

        return [
            'is_active' => 'boolean',
        ];

    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
