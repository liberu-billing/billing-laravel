<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'type',
    'subject',
    'body',
    'team_id',
    'is_default',
])]
class EmailTemplate extends Model
{
    use HasTeam;

    public static function getTemplate($type, $teamId = null)
    {
        return static::where(
            'type',
            $type
        )
            ->where(
                function ($query) use ($teamId): void {
                    $query->where(
                        'team_id',
                        $teamId
                    )
                        ->orWhere(
                            'is_default',
                            true
                        );
                }
            )
            ->orderBy(
                'team_id',
                'desc'
            ) // Prioritize team templates
            ->first();
    }
}
