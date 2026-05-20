<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\KnowledgeBaseService;
use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KnowledgeBaseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected KnowledgeBaseService $kbService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kbService = new KnowledgeBaseService();
    }

    public function test_can_search_articles()
    {
        $user = User::factory()->create();
        $category = KnowledgeBaseCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        KnowledgeBaseArticle::create([
            'category_id' => $category->id,
            'author_id' => $user->id,
            'title' => 'How to reset password',
            'slug' => 'how-to-reset-password',
            'content' => 'This is a guide on resetting your password',
            'is_published' => true,
        ]);

        $results = $this->kbService->search('password');

        $this->assertCount(1, $results);
        $this->assertEquals('How to reset password', $results->first()->title);
    }

    public function test_can_get_featured_articles()
    {
        $user = User::factory()->create();
        $category = KnowledgeBaseCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        KnowledgeBaseArticle::create([
            'category_id' => $category->id,
            'author_id' => $user->id,
            'title' => 'Featured Article',
            'slug' => 'featured-article',
            'content' => 'This is featured',
            'is_published' => true,
            'is_featured' => true,
        ]);

        $featured = $this->kbService->getFeatured();

        $this->assertCount(1, $featured);
        $this->assertTrue($featured->first()->is_featured);
    }

    public function test_can_track_article_views()
    {
        $user = User::factory()->create();
        $category = KnowledgeBaseCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $article = KnowledgeBaseArticle::create([
            'category_id' => $category->id,
            'author_id' => $user->id,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'is_published' => true,
            'view_count' => 0,
        ]);

        $this->kbService->trackView($article);

        $this->assertEquals(1, $article->fresh()->view_count);
    }

    public function test_can_mark_article_as_helpful()
    {
        $user = User::factory()->create();
        $category = KnowledgeBaseCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $article = KnowledgeBaseArticle::create([
            'category_id' => $category->id,
            'author_id' => $user->id,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'is_published' => true,
            'helpful_count' => 0,
        ]);

        $this->kbService->markHelpful($article);

        $this->assertEquals(1, $article->fresh()->helpful_count);
    }

    public function test_can_get_category_tree()
    {
        $parent = KnowledgeBaseCategory::create([
            'name' => 'Parent Category',
            'slug' => 'parent-category',
            'is_active' => true,
        ]);

        $child = KnowledgeBaseCategory::create([
            'name' => 'Child Category',
            'slug' => 'child-category',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $tree = $this->kbService->getCategoryTree();

        $this->assertCount(1, $tree);
        $this->assertEquals('Parent Category', $tree->first()->name);
        $this->assertCount(1, $tree->first()->children);
    }
}
