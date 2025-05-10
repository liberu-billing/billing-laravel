<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class EmailTemplate extends Model
{
    use HasFactory, HasTeam;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'body',
        'team_id',
        'is_default'
    ];

    public static function getTemplate($type, $teamId = null)
    {
        return static::where('type', $type)
            ->where(function ($query) use ($teamId) {
                $query->where('team_id', $teamId)
                    ->orWhere('is_default', true);
            })
            ->orderBy('team_id', 'desc') // Prioritize team templates
            ->first();
    }
}