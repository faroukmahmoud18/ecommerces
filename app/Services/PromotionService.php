
<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PromotionService
{
    /**
     * Apply promotions to an order.
     *
     * @param Order $order
     * @return array
     */
    public function applyPromotions(Order $order)
    {
        try {
            $appliedPromotions = [];
            $totalDiscount = 0;

            // Get applicable promotions
            $promotions = $this->getApplicablePromotions($order);

            foreach ($promotions as $promotion) {
                // Check if promotion is valid
                if (!$this->isPromotionValid($promotion, $order)) {
                    continue;
                }

                // Calculate discount
                $discount = $this->calculatePromotionDiscount($promotion, $order);

                if ($discount > 0) {
                    // Apply promotion
                    $order->promotions()->attach($promotion->id, [
                        'discount_amount' => $discount,
                        'applied_at' => now(),
                    ]);

                    $appliedPromotions[] = [
                        'id' => $promotion->id,
                        'name' => $promotion->name,
                        'type' => $promotion->type,
                        'discount_amount' => $discount,
                        'discount_percentage' => $promotion->discount_percentage,
                        'discount_fixed' => $promotion->discount_fixed,
                    ];

                    $totalDiscount += $discount;
                }
            }

            // Update order with total discount
            $order->update([
                'discount_amount' => $totalDiscount,
                'total_amount' => $order->subtotal + $order->shipping_cost + $order->tax_amount - $totalDiscount,
            ]);

            return [
                'success' => true,
                'applied_promotions' => $appliedPromotions,
                'total_discount' => $totalDiscount,
            ];
        } catch (\Exception $e) {
            Log::error('Error applying promotions: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تطبيق العروض الترويجية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get applicable promotions for an order.
     *
     * @param Order $order
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getApplicablePromotions(Order $order)
    {
        return Promotion::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->where('end_date', '>=', now())
                  ->orWhereNull('end_date');
            })
            ->where(function($q) {
                $q->where('usage_limit', '>', 0)
                  ->orWhereNull('usage_limit');
            })
            ->where(function($q) {
                $q->where('usage_count', '<', DB::raw('usage_limit'))
                  ->orWhereNull('usage_limit');
            })
            ->get();
    }

    /**
     * Check if a promotion is valid for an order.
     *
     * @param Promotion $promotion
     * @param Order $order
     * @return bool
     */
    private function isPromotionValid(Promotion $promotion, Order $order)
    {
        // Check if promotion has reached usage limit
        if ($promotion->usage_limit && $promotion->usage_count >= $promotion->usage_limit) {
            return false;
        }

        // Check minimum order amount
        if ($promotion->min_order_amount && $order->subtotal < $promotion->min_order_amount) {
            return false;
        }

        // Check user eligibility
        if ($promotion->user_type === 'new' && !$order->user->isNewCustomer()) {
            return false;
        }

        if ($promotion->user_type === 'returning' && $order->user->isNewCustomer()) {
            return false;
        }

        // Check if promotion is for specific users
        if ($promotion->user_ids && !in_array($order->user_id, $promotion->user_ids)) {
            return false;
        }

        // Check if promotion is for specific vendors
        if ($promotion->vendor_ids && !in_array($order->vendor_id, $promotion->vendor_ids)) {
            return false;
        }

        // Check if promotion is for specific categories
        if ($promotion->category_ids) {
            $orderCategoryIds = $order->items->flatMap(function($item) {
                return $item->product->categories->pluck('id');
            })->unique();

            if ($orderCategoryIds->intersect($promotion->category_ids)->isEmpty()) {
                return false;
            }
        }

        // Check if promotion is for specific products
        if ($promotion->product_ids) {
            $orderProductIds = $order->items->pluck('product_id');

            if ($orderProductIds->intersect($promotion->product_ids)->isEmpty()) {
                return false;
            }
        }

        // Check if promotion is for specific customer groups
        if ($promotion->customer_group_ids) {
            // Assuming users have customer groups
            $userGroupIds = $order->user->customerGroups->pluck('id');

            if ($userGroupIds->intersect($promotion->customer_group_ids)->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate promotion discount for an order.
     *
     * @param Promotion $promotion
     * @param Order $order
     * @return float
     */
    private function calculatePromotionDiscount(Promotion $promotion, Order $order)
    {
        $discount = 0;

        switch ($promotion->type) {
            case 'percentage':
                // Calculate percentage discount
                if ($promotion->discount_percentage) {
                    $discount = $order->subtotal * ($promotion->discount_percentage / 100);

                    // Apply maximum discount limit if set
                    if ($promotion->max_discount && $discount > $promotion->max_discount) {
                        $discount = $promotion->max_discount;
                    }
                }
                break;

            case 'fixed':
                // Calculate fixed discount
                if ($promotion->discount_fixed) {
                    $discount = $promotion->discount_fixed;
                }
                break;

            case 'buy_x_get_y':
                // Calculate buy X get Y discount
                if ($promotion->buy_x && $promotion->get_y) {
                    $applicableProductIds = $promotion->product_ids ?: $order->items->pluck('product_id');

                    foreach ($applicableProductIds as $productId) {
                        $productItems = $order->items->where('product_id', $productId);
                        $quantity = $productItems->sum('quantity');

                        if ($quantity >= $promotion->buy_x) {
                            $freeItems = floor($quantity / $promotion->buy_x) * $promotion->get_y;

                            // Calculate discount for free items
                            $productPrice = $productItems->first()->price;
                            $discount += $freeItems * $productPrice;
                        }
                    }
                }
                break;

            case 'free_shipping':
                // Calculate free shipping discount
                if ($promotion->free_shipping && $order->shipping_cost > 0) {
                    $discount = $order->shipping_cost;
                }
                break;
        }

        // Apply discount limit if set
        if ($promotion->discount_limit && $discount > $promotion->discount_limit) {
            $discount = $promotion->discount_limit;
        }

        return $discount;
    }

    /**
     * Create a new promotion.
     *
     * @param array $data
     * @return Promotion
     */
    public function createPromotion(array $data)
    {
        try {
            $promotion = Promotion::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'type' => $data['type'],
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'discount_fixed' => $data['discount_fixed'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'min_order_amount' => $data['min_order_amount'] ?? null,
                'usage_limit' => $data['usage_limit'] ?? null,
                'discount_limit' => $data['discount_limit'] ?? null,
                'max_discount' => $data['max_discount'] ?? null,
                'user_type' => $data['user_type'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Attach categories if provided
            if (!empty($data['category_ids'])) {
                $promotion->categories()->attach($data['category_ids']);
            }

            // Attach products if provided
            if (!empty($data['product_ids'])) {
                $promotion->products()->attach($data['product_ids']);
            }

            // Attach users if provided
            if (!empty($data['user_ids'])) {
                $promotion->users()->attach($data['user_ids']);
            }

            // Attach vendors if provided
            if (!empty($data['vendor_ids'])) {
                $promotion->vendors()->attach($data['vendor_ids']);
            }

            // Attach customer groups if provided
            if (!empty($data['customer_group_ids'])) {
                $promotion->customerGroups()->attach($data['customer_group_ids']);
            }

            return $promotion;
        } catch (\Exception $e) {
            Log::error('Error creating promotion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a promotion.
     *
     * @param Promotion $promotion
     * @param array $data
     * @return Promotion
     */
    public function updatePromotion(Promotion $promotion, array $data)
    {
        try {
            $promotion->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'type' => $data['type'],
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'discount_fixed' => $data['discount_fixed'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'min_order_amount' $data['min_order_amount'] ?? null,
                'usage_limit' => $data['usage_limit'] ?? null,
                'discount_limit' => $data['discount_limit'] ?? null,
                'max_discount' => $data['max_discount'] ?? null,
                'user_type' => $data['user_type'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Sync categories if provided
            if (isset($data['category_ids'])) {
                $promotion->categories()->sync($data['category_ids']);
            }

            // Sync products if provided
            if (isset($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            // Sync users if provided
            if (isset($data['user_ids'])) {
                $promotion->users()->sync($data['user_ids']);
            }

            // Sync vendors if provided
            if (isset($data['vendor_ids'])) {
                $promotion->vendors()->sync($data['vendor_ids']);
            }

            // Sync customer groups if provided
            if (isset($data['customer_group_ids'])) {
                $promotion->customerGroups()->sync($data['customer_group_ids']);
            }

            return $promotion;
        } catch (\Exception $e) {
            Log::error('Error updating promotion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a promotion.
     *
     * @param Promotion $promotion
     * @return bool
     */
    public function deletePromotion(Promotion $promotion)
    {
        try {
            $promotion->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting promotion: ' . $e->getMessage());
            return false;
        }
    }
}
