

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class ReminderSetting extends Model
{
    use HasFactory, HasTeam;

    protected $fillable = [
        'team_id',
        'days_before_reminder',
        'reminder_frequency',
        'max_reminders',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}