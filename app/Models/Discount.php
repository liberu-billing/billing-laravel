

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class Discount extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type', // percentage or fixed
        'value',
        'currency',
        'start_date',
        'end_date',
        'max_uses',
        'used_count',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isValid()
    {
        return $this->is_active &&
            $this->start_date <= now() &&
            $this->end_date >= now() &&
            ($this->max_uses === null || $this->used_count < $this->max_uses);
    }
}