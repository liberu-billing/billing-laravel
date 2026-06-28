<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $project_id
 * @property string $title
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Project|null $project
 * @property-read Collection<int, TicketResponse> $responses
 */
#[Fillable([
    'user_id',
    'project_id',
    'title',
    'description',
    'status',
    'priority',
])]
class Ticket extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TicketResponse::class);
    }
}
