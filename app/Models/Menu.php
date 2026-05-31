<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'url',
    'parent_id',
    'order',
])]
class Menu extends Model
{
    use HasFactory, SoftDeletes;

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }
}
