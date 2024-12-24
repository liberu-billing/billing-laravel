

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class Report extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'name',
        'type', // revenue, expense, outstanding
        'start_date',
        'end_date',
        'filters',
        'team_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'filters' => 'array'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}