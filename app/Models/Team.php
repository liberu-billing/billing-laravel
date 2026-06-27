<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Override;

#[Fillable([
    'name',
    'personal_team',
    'user_id',
    'is_default_for_registration',
])]
class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'name',
        'personal_team',
        'user_id',
        'is_default_for_registration',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    #[Override]
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'is_default_for_registration' => 'boolean',
        ];
    }

    /**
     * Enforce a single default-for-registration team: turning the flag on for one
     * team clears it on every other. Done via the query builder (not model events)
     * so it does not recurse through this saving hook.
     */
    protected static function booted(): void
    {
        static::saving(function (Team $team): void {
            if ($team->is_default_for_registration && $team->isDirty('is_default_for_registration')) {
                static::query()
                    ->where('is_default_for_registration', true)
                    ->when($team->exists, fn ($query) => $query->whereKeyNot($team->getKey()))
                    ->update(['is_default_for_registration' => false]);
            }
        });
    }

    /**
     * The team new registrants are attached to, if an admin has designated one.
     */
    public static function defaultForRegistration(): ?self
    {
        return static::query()->where('is_default_for_registration', true)->first();
    }
}
