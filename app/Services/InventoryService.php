<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryLog;
use App\Models\Warehouse;
use App\Models\StockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryService
{
    /**
     * Update product quantity.
     *
     * @param Product $product
     * @param int $quantity
     * @param string $action ('add', 'subtract')
     * @param string $reason
     * @param int $userId
     * @return array
     */
    public function updateProductQuantity(Product $product, $quantity, $action, $reason, $userId = null)
    {
        DB::beginTransaction();

        try {
            if ($action === 'add') {
                $product->quantity += $quantity;
            } elseif ($action === 'subtract') {
                if ($product->quantity < $quantity) {
                    return [
                        'success' => false,
                        'message' => 'الكمية المطلوبة أكبر من الكمية المتوفرة',
                    ];
                }
                $product->quantity -= $quantity;
            } else {
                return [
                    'success' => false,
                    'message' => 'إجراء غير صالح',
                ];
            }

            $product->save();

            // Create inventory log
            $this->createInventoryLog($product, $quantity, $action, $reason, $userId);

            // Check for low stock alerts
            $this->checkLowStockAlert($product);

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم تحديث الكمية بنجاح',
                'new_quantity' => $product->quantity,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating product quantity: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الكمية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create inventory log.
     *
     * @param Product $product
     * @param int $quantity
     * @param string $action
     * @param string $reason
     * @param int $userId
     */
    private function createInventoryLog(Product $product, $quantity, $action, $reason, $userId = null)
    {
        InventoryLog::create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'action' => $action,
            'previous_quantity' => $product->quantity - ($action === 'add' ? $quantity : -$quantity),
            'new_quantity' => $product->quantity,
            'reason' => $reason,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }

    /**
     * Check for low stock alerts.
     *
     * @param Product $product
     */
    private function checkLowStockAlert(Product $product)
    {
        if ($product->manage_inventory) {
            if ($product->quantity <= config('app.low_stock_threshold', 10) && $product->quantity > 0) {
                // Check if alert already exists
                $existingAlert = StockAlert::where('product_id', $product->id)
                    ->where('type', 'low_stock')
                    ->where('resolved', false)
                    ->first();

                if (!$existingAlert) {
                    StockAlert::create([
                        'product_id' => $product->id,
                        'type' => 'low_stock',
                        'message' => 'الكمية الحالية للمنتج ' . $product->name . ' منخفضة (الكمية: ' . $product->quantity . ')',
                        'severity' => 'medium',
                        'resolved' => false,
                    ]);
                }
            } elseif ($product->quantity === 0) {
                // Check if alert already exists
                $existingAlert = StockAlert::where('product_id', $product->id)
                    ->where('type', 'out_of_stock')
                    ->where('resolved', false)
                    ->first();

                if (!$existingAlert) {
                    StockAlert::create([
                        'product_id' => $product->id,
                        'type' => 'out_of_stock',
                        'message' => 'المنتج ' . $product->name . ' غير متوفر في المخزون',
                        'severity' => 'high',
                        'resolved' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Get inventory reports.
     *
     * @param array $filters
     * @return array
     */
    public function getInventoryReports($filters = [])
    {
        try {
            // Get low stock products
            $lowStockProducts = Product::where('manage_inventory', true)
                ->where('quantity', '<=', config('app.low_stock_threshold', 10))
                ->where('quantity', '>', 0)
                ->with('vendor')
                ->get();

            // Get out of stock products
            $outOfStockProducts = Product::where('manage_inventory', true)
                ->where('quantity', 0)
                ->with('vendor')
                ->get();

            // Get products with high stock
            $highStockProducts = Product::where('manage_inventory', true)
                ->where('quantity', '>', config('app.high_stock_threshold', 100))
                ->with('vendor')
                ->get();

            // Get inventory value
            $inventoryValue = Product::where('manage_inventory', true)
                ->select(DB::raw('SUM(price * quantity) as total_value'))
                ->value('total_value') ?? 0;

            // Get inventory by category
            $inventoryByCategory = Product::where('manage_inventory', true)
                ->join('product_category', 'products.id', '=', 'product_category.product_id')
                ->join('categories', 'product_category.category_id', '=', 'categories.id')
                ->groupBy('categories.id', 'categories.name')
                ->select('categories.id', 'categories.name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(price * quantity) as total_value'))
                ->orderBy('total_value', 'desc')
                ->get();

            // Get inventory by vendor
            $inventoryByVendor = Product::where('manage_inventory', true)
                ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
                ->groupBy('vendors.id', 'vendors.name')
                ->select('vendors.id', 'vendors.name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(price * quantity) as total_value'))
                ->orderBy('total_value', 'desc')
                ->get();

            // Get inventory movements
            $inventoryMovements = InventoryLog::with(['product', 'user'])
                ->when(!empty($filters['start_date']), function($query) use ($filters) {
                    $query->whereDate('created_at', '>=', $filters['start_date']);
                })
                ->when(!empty($filters['end_date']), function($query) use ($filters) {
                    $query->whereDate('created_at', '<=', $filters['end_date']);
                })
                ->when(!empty($filters['product_id']), function($query) use ($filters) {
                    $query->where('product_id', $filters['product_id']);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return [
                'success' => true,
                'data' => [
                    'low_stock_products' => $lowStockProducts,
                    'out_of_stock_products' => $outOfStockProducts,
                    'high_stock_products' => $highStockProducts,
                    'inventory_value' => $inventoryValue,
                    'inventory_by_category' => $inventoryByCategory,
                    'inventory_by_vendor' => $inventoryByVendor,
                    'inventory_movements' => $inventoryMovements,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting inventory reports: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على تقارير المخزون',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get stock alerts.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockAlerts($filters = [])
    {
        return StockAlert::with(['product'])
            ->when(!empty($filters['type']), function($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(!empty($filters['resolved']), function($query) use ($filters) {
                $query->where('resolved', $filters['resolved']);
            })
            ->when(!empty($filters['severity']), function($query) use ($filters) {
                $query->where('severity', $filters['severity']);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Resolve a stock alert.
     *
     * @param StockAlert $alert
     * @param string $resolution
     * @return bool
     */
    public function resolveStockAlert(StockAlert $alert, $resolution)
    {
        try {
            $alert->update([
                'resolved' => true,
                'resolution' => $resolution,
                'resolved_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error resolving stock alert: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Create product variants.
     *
     * @param Product $product
     * @param array $variants
     * @return array
     */
    public function createProductVariants(Product $product, array $variants)
    {
        DB::beginTransaction();

        try {
            foreach ($variants as $variant) {
                // Create variant
                $productVariant = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variant['name'],
                    'sku' => $variant['sku'],
                    'price' => $variant['price'],
                    'quantity' => $variant['quantity'],
                    'image' => $variant['image'] ?? null,
                    'manage_inventory' => $product->manage_inventory,
                ]);

                // Create inventory log
                $this->createInventoryLog(
                    $product, 
                    $variant['quantity'], 
                    'add', 
                    'إنشاء خيار للمنتج: ' . $product->name . ' - ' . $variant['name']
                );

                // Check for low stock alerts
                $this->checkLowStockAlert($product);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم إنشاء الخيارات بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating product variants: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الخيارات',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update product variant quantity.
     *
     * @param ProductVariant $variant
     * @param int $quantity
     * @param string $action ('add', 'subtract')
     * @param string $reason
     * @param int $userId
     * @return array
     */
    public function updateVariantQuantity(ProductVariant $variant, $quantity, $action, $reason, $userId = null)
    {
        DB::beginTransaction();

        try {
            if ($action === 'add') {
                $variant->quantity += $quantity;
            } elseif ($action === 'subtract') {
                if ($variant->quantity < $quantity) {
                    return [
                        'success' => false,
                        'message' => 'الكمية المطلوبة أكبر من الكمية المتوفرة',
                    ];
                }
                $variant->quantity -= $quantity;
            } else {
                return [
                    'success' => false,
                    'message' => 'إجراء غير صالح',
                ];
            }

            $variant->save();

            // Update product quantity
            $product = $variant->product;
            $productQuantityChange = $action === 'add' ? $quantity : -$quantity;

            $product->quantity += $productQuantityChange;
            $product->save();

            // Create inventory log
            $this->createInventoryLog(
                $product, 
                $quantity, 
                $action, 
                'تحديث كمية الخيار للمنتج: ' . $product->name . ' - ' . $variant->name . ' - السبب: ' . $reason,
                $userId
            );

            // Check for low stock alerts
            $this->checkLowStockAlert($product);

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم تحديث الكمية بنجاح',
                'new_quantity' => $variant->quantity,
                'product_quantity' => $product->quantity,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating variant quantity: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الكمية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get inventory valuation report.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getInventoryValuationReport($startDate, $endDate)
    {
        try {
            // Get inventory valuation by category
            $valuationByCategory = Product::where('manage_inventory', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->join('product_category', 'products.id', '=', 'product_category.product_id')
                ->join('categories', 'product_category.category_id', '=', 'categories.id')
                ->groupBy('categories.id', 'categories.name')
                ->select('categories.id', 'categories.name', DB::raw('SUM(price * quantity) as total_value'))
                ->orderBy('total_value', 'desc')
                ->get();

            // Get inventory valuation by vendor
            $valuationByVendor = Product::where('manage_inventory', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
                ->groupBy('vendors.id', 'vendors.name')
                ->select('vendors.id', 'vendors.name', DB::raw('SUM(price * quantity) as total_value'))
                ->orderBy('total_value', 'desc')
                ->get();

            // Get inventory movements
            $movements = InventoryLog::with(['product', 'user'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Calculate total valuation
            $totalValuation = $movements->sum(DB::raw('quantity * (
                SELECT price FROM products WHERE id = inventory_logs.product_id LIMIT 1
            )'));

            return [
                'success' => true,
                'data' => [
                    'valuation_by_category' => $valuationByCategory,
                    'valuation_by_vendor' => $valuationByVendor,
                    'movements' => $movements,
                    'total_valuation' => $totalValuation,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting inventory valuation report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على تقييم المخزون',
                'error' => $e->getMessage(),
            ];
        }
    }
}
