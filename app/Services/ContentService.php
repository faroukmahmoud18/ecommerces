
<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use App\Models\Category as BlogCategory;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ContentService
{
    /**
     * Create a new page.
     *
     * @param array $data
     * @return array
     */
    public function createPage(array $data)
    {
        DB::beginTransaction();

        try {
            // Create page
            $page = Page::create([
                'title' => $data['title'],
                'slug' => $data['slug'] ?? str_slug($data['title']),
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'featured_image' => $data['featured_image'] ?? null,
                'template' => $data['template'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'position' => $data['position'] ?? 0,
                'author_id' => auth()->id(),
                'published_at' => $data['published_at'] ?? null,
            ]);

            // Add page to menu if specified
            if (!empty($data['menu_id'])) {
                $this->addPageToMenu($page, $data['menu_id'], $data['menu_position'] ?? null);
            }

            DB::commit();

            return [
                'success' => true,
                'page_id' => $page->id,
                'message' => 'تم إنشاء الصفحة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating page: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الصفحة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add page to menu.
     *
     * @param Page $page
     * @param int $menuId
     * @param int $position
     */
    private function addPageToMenu(Page $page, $menuId, $position = null)
    {
        $menu = Menu::find($menuId);

        if ($menu) {
            $menuItem = MenuItem::create([
                'menu_id' => $menuId,
                'title' => $page->title,
                'url' => route('pages.show', $page->slug),
                'target' => '_self',
                'icon_class' => null,
                'order' => $position ?? $menu->items()->count() + 1,
                'parent_id' => null,
            ]);
        }
    }

    /**
     * Create a new blog post.
     *
     * @param array $data
     * @return array
     */
    public function createPost(array $data)
    {
        DB::beginTransaction();

        try {
            // Create post
            $post = Post::create([
                'title' => $data['title'],
                'slug' => $data['slug'] ?? str_slug($data['title']),
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'featured_image' => $data['featured_image'] ?? null,
                'author_id' => auth()->id(),
                'published_at' => $data['published_at'] ?? null,
            ]);

            // Add categories
            if (!empty($data['categories'])) {
                $post->categories()->attach($data['categories']);
            }

            // Add tags
            if (!empty($data['tags'])) {
                $post->tags()->sync($data['tags']);
            }

            DB::commit();

            return [
                'success' => true,
                'post_id' => $post->id,
                'message' => 'تم إنشاء المقال بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating post: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء المقال',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new menu.
     *
     * @param array $data
     * @return array
     */
    public function createMenu(array $data)
    {
        DB::beginTransaction();

        try {
            // Create menu
            $menu = Menu::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'created_by' => auth()->id(),
            ]);

            // Add menu items if provided
            if (!empty($data['items'])) {
                $this->addMenuItems($menu, $data['items']);
            }

            DB::commit();

            return [
                'success' => true,
                'menu_id' => $menu->id,
                'message' => 'تم إنشاء القائمة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating menu: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء القائمة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add menu items to a menu.
     *
     * @param Menu $menu
     * @param array $items
     * @param int $parentId
     */
    private function addMenuItems(Menu $menu, array $items, $parentId = null)
    {
        foreach ($items as $item) {
            $menuItem = MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'url' => $item['url'] ?? null,
                'target' => $item['target'] ?? '_self',
                'icon_class' => $item['icon_class'] ?? null,
                'order' => $item['order'] ?? 0,
                'parent_id' => $parentId,
            ]);

            // Add children if any
            if (!empty($item['children'])) {
                $this->addMenuItems($menu, $item['children'], $menuItem->id);
            }
        }
    }

    /**
     * Get content statistics.
     *
     * @return array
     */
    public function getContentStatistics()
    {
        try {
            // Get page statistics
            $pageStats = [
                'total_pages' => Page::count(),
                'published_pages' => Page::where('status', 'published')->count(),
                'draft_pages' => Page::where('status', 'draft')->count(),
                'total_views' => Page::sum('views'),
            ];

            // Get post statistics
            $postStats = [
                'total_posts' => Post::count(),
                'published_posts' => Post::where('status', 'published')->count(),
                'draft_posts' => Post::where('status', 'draft')->count(),
                'total_views' => Post::sum('views'),
                'total_comments' => Comment::count(),
                'approved_comments' => Comment::where('status', 'approved')->count(),
                'pending_comments' => Comment::where('status', 'pending')->count(),
            ];

            // Get category statistics
            $categoryStats = [
                'total_categories' => BlogCategory::count(),
                'active_categories' => BlogCategory::where('status', 'active')->count(),
            ];

            // Get tag statistics
            $tagStats = [
                'total_tags' => Tag::count(),
                'popular_tags' => Tag::withCount('posts')->orderBy('posts_count', 'desc')->take(5)->get(),
            ];

            // Get menu statistics
            $menuStats = [
                'total_menus' => Menu::count(),
                'active_menus' => Menu::where('status', 'active')->count(),
                'total_menu_items' => MenuItem::count(),
            ];

            return [
                'success' => true,
                'data' => [
                    'pages' => $pageStats,
                    'posts' => $postStats,
                    'categories' => $categoryStats,
                    'tags' => $tagStats,
                    'menus' => $menuStats,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting content statistics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على إحصائيات المحتوى',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get popular posts.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularPosts($limit = 5)
    {
        return Post::with('categories')
            ->where('status', 'published')
            ->orderBy('views', 'desc')
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get recent posts.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentPosts($limit = 5)
    {
        return Post::with('categories')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get related posts.
     *
     * @param Post $post
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedPosts(Post $post, $limit = 3)
    {
        // Get related posts by category
        $relatedPosts = Post::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->whereHas('categories', function($query) use ($post) {
                $query->whereIn('categories.id', $post->categories->pluck('id'));
            })
            ->inRandomOrder()
            ->take($limit)
            ->get();

        // If we don't have enough posts, get more by tag
        if ($relatedPosts->count() < $limit) {
            $remaining = $limit - $relatedPosts->count();

            $tagRelatedPosts = Post::where('status', 'published')
                ->where('id', '!=', $post->id)
                ->whereHas('tags', function($query) use ($post) {
                    $query->whereIn('tags.id', $post->tags->pluck('id'));
                })
                ->whereDoesntHave('categories', function($query) use ($post) {
                    $query->whereIn('categories.id', $post->categories->pluck('id'));
                })
                ->inRandomOrder()
                ->take($remaining)
                ->get();

            $relatedPosts = $relatedPosts->merge($tagRelatedPosts);
        }

        return $relatedPosts;
    }

    /**
     * Search content.
     *
     * @param string $query
     * @param string $type ('pages', 'posts', 'all')
     * @return array
     */
    public function searchContent($query, $type = 'all')
    {
        try {
            $results = [];

            if ($type === 'pages' || $type === 'all') {
                $pages = Page::where('status', 'published')
                    ->where('title', 'like', '%' . $query . '%')
                    ->orWhere('content', 'like', '%' . $query . '%')
                    ->take(5)
                    ->get();

                foreach ($pages as $page) {
                    $results[] = [
                        'type' => 'page',
                        'id' => $page->id,
                        'title' => $page->title,
                        'url' => route('pages.show', $page->slug),
                        'excerpt' => $page->excerpt,
                    ];
                }
            }

            if ($type === 'posts' || $type === 'all') {
                $posts = Post::where('status', 'published')
                    ->where('title', 'like', '%' . $query . '%')
                    ->orWhere('content', 'like', '%' . $query . '%')
                    ->take(5)
                    ->get();

                foreach ($posts as $post) {
                    $results[] = [
                        'type' => 'post',
                        'id' => $post->id,
                        'title' => $post->title,
                        'url' => route('blog.show', $post->slug),
                        'excerpt' => $post->excerpt,
                    ];
                }
            }

            return [
                'success' => true,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Error searching content: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث عن المحتوى',
                'error' => $e->getMessage(),
            ];
        }
    }
}
