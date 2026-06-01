<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'team_id',
    'days_before_reminder',
    'reminder_frequency',
    'max_reminders',
    'is_active',
])]
class ReminderSetting extends Model
{
    use HasFactory, HasTeam;

    #[\Override]
    protected function casts(): array
    {

        return [
            'is_active' => 'boolean',
        ];

    }
}
