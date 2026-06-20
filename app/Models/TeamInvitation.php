<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\TeamInvitation as JetstreamTeamInvitation;

#[Fillable([
    'email',
    'role',
])]
class TeamInvitation extends JetstreamTeamInvitation
{
    /**
     * Bootstrap the model and its traits.
     */
    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(
            static function ($invitation): void {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
        });
    }

    /**
     * Get the team that the invitation belongs to.
     */
    #[\Override]
    public function team(): BelongsTo
    {
        return $this->belongsTo(Jetstream::teamModel());
    }
}
