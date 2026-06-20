<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'email',
    'phone',
    'address',
    'company',
    'notes',
    'status',
])]
class Client extends Model
{

    #[\Override]
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }
}
