<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company',
        'notes',
        'status'
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}