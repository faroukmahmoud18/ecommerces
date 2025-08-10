
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecommendationService
{
    /**
     * Get personalized product recommendations for a user.
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPersonalizedRecommendations(User $user, $limit = 10)
    {
        try {
            // Get user's purchase history
            $userCategories = $this->getUserCategories($user);
            $userBrands = $this->getUserBrands($user);
            $priceRange = $this->getUserPriceRange($user);

            // Get products based on user's purchase history
            $query = Product::query()
                ->where('is_active', true)
                ->where('is_approved', true);

            if (!empty($userCategories)) {
                $query->whereHas('categories', function($q) use ($userCategories) {
                    $q->whereIn('categories.id', $userCategories);
                });
            }

            if (!empty($userBrands)) {
                // Assuming products have a 'brand' attribute
                $query->whereIn('brand', $userBrands);
            }

            if (!empty($priceRange)) {
                $query->whereBetween('price', $priceRange);
            }

            // Exclude products the user has already purchased
            $purchasedProductIds = OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('customer_id', $user->id);
            })->pluck('product_id');

            if ($purchasedProductIds->isNotEmpty()) {
                $query->whereNotIn('id', $purchasedProductIds);
            }

            // Get products based on popularity and user preferences
            $recommendations = $query->orderBy('views', 'desc')
                ->orderBy('sales_count', 'desc')
                ->orderBy('rating', 'desc')
                ->take($limit)
                ->get();

            // If not enough recommendations, get popular products
            if ($recommendations->count() < $limit) {
                $popularProducts = Product::where('is_active', true)
                    ->where('is_approved', true)
                    ->whereNotIn('id', $purchasedProductIds)
                    ->orderBy('views', 'desc')
                    ->orderBy('sales_count', 'desc')
                    ->orderBy('rating', 'desc')
                    ->take($limit - $recommendations->count())
                    ->get();

                $recommendations = $recommendations->merge($popularProducts);
            }

            return $recommendations;
        } catch (\Exception $e) {
            Log::error('Error getting personalized recommendations: ' . $e->getMessage());

            return collect();
        }
    }

    /**
     * Get similar products based on product attributes.
     *
     * @param Product $product
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSimilarProducts(Product $product, $limit = 10)
    {
        try {
            // Get products from the same category
            $similarProducts = Product::where('id', '!=', $product->id)
                ->where('is_active', true)
                ->where('is_approved', true)
                ->whereHas('categories', function($q) use ($product) {
                    $q->whereIn('categories.id', $product->categories->pluck('id'));
                })
                ->orderBy('views', 'desc')
                ->orderBy('sales_count', 'desc')
                ->orderBy('rating', 'desc')
                ->take($limit)
                ->get();

            // If not enough recommendations, get products from parent categories
            if ($similarProducts->count() < $limit) {
                $parentCategoryIds = Category::where('parent_id', '!=', null)
                    ->whereIn('id', $product->categories->pluck('parent_id'))
                    ->pluck('id');

                if ($parentCategoryIds->isNotEmpty()) {
                    $parentProducts = Product::where('id', '!=', $product->id)
                        ->where('is_active', true)
                        ->where('is_approved', true)
                        ->whereHas('categories', function($q) use ($parentCategoryIds) {
                            $q->whereIn('categories.id', $parentCategoryIds);
                        })
                        ->whereNotIn('id', $similarProducts->pluck('id'))
                        ->orderBy('views', 'desc')
                        ->orderBy('sales_count', 'desc')
                        ->orderBy('rating', 'desc')
                        ->take($limit - $similarProducts->count())
                        ->get();

                    $similarProducts = $similarProducts->merge($parentProducts);
                }
            }

            // If still not enough, get popular products
            if ($similarProducts->count() < $limit) {
                $popularProducts = Product::where('id', '!=', $product->id)
                    ->where('is_active', true)
                    ->where('is_approved', true)
                    ->whereNotIn('id', $similarProducts->pluck('id'))
                    ->orderBy('views', 'desc')
                    ->orderBy('sales_count', 'desc')
                    ->orderBy('rating', 'desc')
                    ->take($limit - $similarProducts->count())
                    ->get();

                $similarProducts = $similarProducts->merge($popularProducts);
            }

            return $similarProducts;
        } catch (\Exception $e) {
            Log::error('Error getting similar products: ' . $e->getMessage());

            return collect();
        }
    }

    /**
     * Get trending products.
     *
     * @param int $limit
     * @param string $period ('day', 'week', 'month', 'year')
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrendingProducts($limit = 10, $period = 'week')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subWeek(),
            };

            $trendingProducts = Product::where('is_active', true)
                ->where('is_approved', true)
                ->whereHas('orderItems', function($q) use ($startDate) {
                    $q->whereHas('order', function($orderQuery) use ($startDate) {
                        $orderQuery->where('created_at', '>=', $startDate);
                    });
                })
                ->withCount(['orderItems as recent_sales' => function($q) use ($startDate) {
                    $q->whereHas('order', function($orderQuery) use ($startDate) {
                        $orderQuery->where('created_at', '>=', $startDate);
                    })->select(DB::raw('COUNT(DISTINCT order_items.id)'));
                }])
                ->withCount(['views as recent_views' => function($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                }])
                ->orderBy('recent_sales', 'desc')
                ->orderBy('recent_views', 'desc')
                ->orderBy('rating', 'desc')
                ->take($limit)
                ->get();

            return $trendingProducts;
        } catch (\Exception $e) {
            Log::error('Error getting trending products: ' . $e->getMessage());

            return collect();
        }
    }

    /**
     * Get frequently bought together products.
     *
     * @param Product $product
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFrequentlyBoughtTogether(Product $product, $limit = 5)
    {
        try {
            // Get orders that contain the given product
            $orderIds = OrderItem::where('product_id', $product->id)
                ->pluck('order_id');

            if ($orderIds->isEmpty()) {
                return collect();
            }

            // Get other products from those orders
            $otherProducts = OrderItem::whereIn('order_id', $orderIds)
                ->where('product_id', '!=', $product->id)
                ->groupBy('product_id')
                ->select('product_id', DB::raw('COUNT(*) as frequency'))
                ->orderBy('frequency', 'desc')
                ->take($limit)
                ->get();

            // Get the product models
            $productIds = $otherProducts->pluck('product_id');
            $products = Product::whereIn('id', $productIds)
                ->where('is_active', true)
                ->where('is_approved', true)
                ->get();

            return $products;
        } catch (\Exception $e) {
            Log::error('Error getting frequently bought together products: ' . $e->getMessage());

            return collect();
        }
    }

    /**
     * Get user's preferred categories.
     *
     * @param User $user
     * @return array
     */
    private function getUserCategories(User $user)
    {
        try {
            $categoryIds = OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('customer_id', $user->id);
            })
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('product_category', 'products.id', '=', 'product_category.product_id')
            ->groupBy('product_category.category_id')
            ->select('product_category.category_id', DB::raw('COUNT(*) as frequency'))
            ->orderBy('frequency', 'desc')
            ->pluck('category_id')
            ->take(5)
            ->toArray();

            return $categoryIds;
        } catch (\Exception $e) {
            Log::error('Error getting user categories: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get user's preferred brands.
     *
     * @param User $user
     * @return array
     */
    private function getUserBrands(User $user)
    {
        try {
            // Assuming products have a 'brand' attribute
            $brands = OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('customer_id', $user->id);
            })
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->groupBy('products.brand')
            ->select('products.brand', DB::raw('COUNT(*) as frequency'))
            ->orderBy('frequency', 'desc')
            ->pluck('brand')
            ->take(5)
            ->toArray();

            return $brands;
        } catch (\Exception $e) {
            Log::error('Error getting user brands: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get user's preferred price range.
     *
     * @param User $user
     * @return array
     */
    private function getUserPriceRange(User $user)
    {
        try {
            $averagePrice = OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('customer_id', $user->id);
            })
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->avg('products.price');

            if ($averagePrice) {
                $minPrice = $averagePrice * 0.7;
                $maxPrice = $averagePrice * 1.3;

                return [$minPrice, $maxPrice];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting user price range: ' . $e->getMessage());

            return [];
        }
    }
}
