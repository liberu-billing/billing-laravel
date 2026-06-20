<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'parent_id',
    'name',
    'slug',
    'description',
    'sort_order',
    'is_active',
])]
class KnowledgeBaseCategory extends Model
{
    #[\Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(
            static function ($category): void {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_id')
            ->orderBy('sort_order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticle::class, 'category_id')
            ->orderBy('sort_order');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->where('is_published', true);
    }
}
