<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'name',
    'type',
    // revenue, expense, outstanding
    'start_date',
    'end_date',
    'filters',
    'format',
    'parameters',
    'schedule',
    'last_generated_at',
    'team_id',
])]
class Report extends Model
{
    use HasTeam;

    #[Override]
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'filters' => 'array',
            'parameters' => 'array',
            'schedule' => 'array',
            'last_generated_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
