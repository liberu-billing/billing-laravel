<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

#[Fillable([
    'team_id',
    'days_before_reminder',
    'reminder_frequency',
    'max_reminders',
    'is_active',
])]
class ReminderSetting extends Model
{
    use HasTeam;

    #[\Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    #[Scope]
    private function isActive(Builder $builder): void
    {
        $builder->where('is_active', 1);
    }
}
