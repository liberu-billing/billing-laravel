

<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'name',
        'country',
        'state',
        'rate',
        'service_type',
        'is_active',
        'team_id'
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}