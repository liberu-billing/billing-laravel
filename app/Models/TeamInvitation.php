<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\TeamInvitation as JetstreamTeamInvitation;

class TeamInvitation extends JetstreamTeamInvitation
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
        });
    }

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Jetstream::teamModel());
    }
}
