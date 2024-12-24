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
        'format',
        'parameters',
        'schedule',
        'last_generated_at',
        'team_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'filters' => 'array'
        'parameters' => 'array',
        'schedule' => 'array',
        'last_generated_at' => 'datetime'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}