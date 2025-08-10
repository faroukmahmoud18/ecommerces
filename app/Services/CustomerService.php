
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Review;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WelcomeNotification;
use App\Notifications\OrderStatusNotification;
use Carbon\Carbon;

class CustomerService
{
    /**
     * Create a new customer.
     *
     * @param array $data
     * @return array
     */
    public function createCustomer(array $data)
    {
        DB::beginTransaction();

        try {
            // Create customer
            $customer = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
                'role' => 'customer',
                'email_verified_at' => $data['email_verified'] ? now() : null,
            ]);

            // Add to customer group if provided
            if (!empty($data['customer_group_id'])) {
                $customer->customerGroups()->attach($data['customer_group_id']);
            }

            // Create address if provided
            if (!empty($data['address'])) {
                Address::create([
                    'user_id' => $customer->id,
                    'type' => 'billing',
                    'first_name' => $data['address']['first_name'] ?? $data['name'],
                    'last_name' => $data['address']['last_name'] ?? '',
                    'company' => $data['address']['company'] ?? null,
                    'address_1' => $data['address']['address_1'] ?? null,
                    'address_2' => $data['address']['address_2'] ?? null,
                    'city' => $data['address']['city'] ?? null,
                    'state' => $data['address']['state'] ?? null,
                    'postal_code' => $data['address']['postal_code'] ?? null,
                    'country' => $data['address']['country'] ?? null,
                    'phone' => $data['address']['phone'] ?? $data['phone'] ?? null,
                    'is_default' => true,
                ]);
            }

            DB::commit();

            // Send welcome notification if email is verified
            if ($customer->email_verified_at) {
                Notification::send($customer, new WelcomeNotification());
            }

            return [
                'success' => true,
                'customer_id' => $customer->id,
                'message' => 'تم إنشاء العميل بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating customer: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء العميل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update customer.
     *
     * @param User $customer
     * @param array $data
     * @return array
     */
    public function updateCustomer(User $customer, array $data)
    {
        DB::beginTransaction();

        try {
            // Update customer
            $customer->update([
                'name' => $data['name'] ?? $customer->name,
                'email' => $data['email'] ?? $customer->email,
                'phone' => $data['phone'] ?? $customer->phone,
                'status' => $data['status'] ?? $customer->status,
                'email_verified_at' => isset($data['email_verified']) && $data['email_verified'] ? now() : $customer->email_verified_at,
            ]);

            // Update password if provided
            if (!empty($data['password'])) {
                $customer->update(['password' => bcrypt($data['password'])]);
            }

            // Update customer groups
            if (isset($data['customer_groups'])) {
                $customer->customerGroups()->sync($data['customer_groups']);
            }

            // Update addresses
            if (!empty($data['addresses'])) {
                // Delete existing addresses
                Address::where('user_id', $customer->id)->delete();

                // Create new addresses
                foreach ($data['addresses'] as $address) {
                    Address::create([
                        'user_id' => $customer->id,
                        'type' => $address['type'],
                        'first_name' => $address['first_name'] ?? $customer->name,
                        'last_name' => $address['last_name'] ?? '',
                        'company' => $address['company'] ?? null,
                        'address_1' => $address['address_1'] ?? null,
                        'address_2' => $address['address_2'] ?? null,
                        'city' => $address['city'] ?? null,
                        'state' => $address['state'] ?? null,
                        'postal_code' => $address['postal_code'] ?? null,
                        'country' => $address['country'] ?? null,
                        'phone' => $address['phone'] ?? $customer->phone ?? null,
                        'is_default' => $address['is_default'] ?? false,
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'customer_id' => $customer->id,
                'message' => 'تم تحديث العميل بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating customer: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث العميل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get customer statistics.
     *
     * @param User $customer
     * @return array
     */
    public function getCustomerStatistics(User $customer)
    {
        try {
            // Get order count
            $orderCount = Order::where('customer_id', $customer->id)->count();

            // Get total spent
            $totalSpent = Order::where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');

            // Get average order value
            $averageOrderValue = $orderCount > 0 ? $totalSpent / $orderCount : 0;

            // Get wishlist count
            $wishlistCount = Wishlist::where('user_id', $customer->id)->count();

            // Get review count
            $reviewCount = Review::where('user_id', $customer->id)->count();

            // Get last order
            $lastOrder = Order::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Get customer group
            $customerGroup = $customer->customerGroups()->first();

            // Get registration date
            $registrationDate = $customer->created_at;

            // Get last login
            $lastLogin = $customer->last_login_at;

            return [
                'success' => true,
                'data' => [
                    'order_count' => $orderCount,
                    'total_spent' => $totalSpent,
                    'average_order_value' => $averageOrderValue,
                    'wishlist_count' => $wishlistCount,
                    'review_count' => $reviewCount,
                    'last_order' => $lastOrder,
                    'customer_group' => $customerGroup,
                    'registration_date' => $registrationDate,
                    'last_login' => $lastLogin,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customer statistics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على إحصائيات العميل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get customer orders.
     *
     * @param User $customer
     * @param array $data
     * @return array
     */
    public function getCustomerOrders(User $customer, array $data = [])
    {
        try {
            $status = $data['status'] ?? null;
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? null;

            // Get orders
            $query = Order::where('customer_id', $customer->id);

            if ($status) {
                $query->where('status', $status);
            }

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($data['per_page'] ?? 10);

            // Calculate totals
            $totalOrders = $orders->total();
            $totalSpent = $orders->sum('total_amount');
            $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

            return [
                'success' => true,
                'data' => [
                    'orders' => $orders,
                    'total_orders' => $totalOrders,
                    'total_spent' => $totalSpent,
                    'average_order_value' => $averageOrderValue,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customer orders: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على طلبات العميل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get customer wishlist.
     *
     * @param User $customer
     * @param array $data
     * @return array
     */
    public function getCustomerWishlist(User $customer, array $data = [])
    {
        try {
            $query = Wishlist::where('user_id', $customer->id);

            $wishlist = $query->with('product')
                ->orderBy('created_at', 'desc')
                ->paginate($data['per_page'] ?? 10);

            return [
                'success' => true,
                'data' => [
                    'wishlist' => $wishlist,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customer wishlist: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على قائمة رغبات العميل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get customer reviews.
     *
     * @param User $customer
     * @param array $data
     * @return array
     */
    public function getCustomerReviews(User $customer, array $data = [])
    {
        try {
            $query = Review::where('user_id', $customer->id);

            $reviews = $query->with('product')
                ->orderBy('created_at', 'desc')
                ->paginate($data['per_page'] ?? 10);

            // Calculate average rating
            $averageRating = $reviews->avg('rating');

            return [
                'success' => true,
                'data' => [
                    'reviews' => $reviews,
                    'average_rating' => $averageRating,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customer reviews: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على مراجعات العميل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update customer status.
     *
     * @param User $customer
     * @param string $status
     * @return array
     */
    public function updateCustomerStatus(User $customer, $status)
    {
        try {
            $customer->update(['status' => $status]);

            return [
                'success' => true,
                'customer_id' => $customer->id,
                'message' => 'تم تحديث حالة العميل بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error updating customer status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة العميل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send order status notification.
     *
     * @param User $customer
     * @param Order $order
     * @return array
     */
    public function sendOrderStatusNotification(User $customer, Order $order)
    {
        try {
            Notification::send($customer, new OrderStatusNotification($order));

            return [
                'success' => true,
                'message' => 'تم إرسال إشعار حالة الطلب بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error sending order status notification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال إشعار حالة الطلب',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get customer groups.
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        try {
            $groups = CustomerGroup::all();

            return [
                'success' => true,
                'data' => [
                    'groups' => $groups,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customer groups: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على مجموعات العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create customer group.
     *
     * @param array $data
     * @return array
     */
    public function createCustomerGroup(array $data)
    {
        try {
            $group = CustomerGroup::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'status' => $data['status'] ?? 'active',
            ]);

            return [
                'success' => true,
                'group_id' => $group->id,
                'message' => 'تم إنشاء مجموعة العملاء بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error creating customer group: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء مجموعة العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update customer group.
     *
     * @param CustomerGroup $group
     * @param array $data
     * @return array
     */
    public function updateCustomerGroup(CustomerGroup $group, array $data)
    {
        try {
            $group->update([
                'name' => $data['name'] ?? $group->name,
                'description' => $data['description'] ?? $group->description,
                'discount_percentage' => $data['discount_percentage'] ?? $group->discount_percentage,
                'status' => $data['status'] ?? $group->status,
            ]);

            return [
                'success' => true,
                'group_id' => $group->id,
                'message' => 'تم تحديث مجموعة العملاء بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error updating customer group: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث مجموعة العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete customer group.
     *
     * @param CustomerGroup $group
     * @return array
     */
    public function deleteCustomerGroup(CustomerGroup $group)
    {
        try {
            $group->delete();

            return [
                'success' => true,
                'group_id' => $group->id,
                'message' => 'تم حذف مجموعة العملاء بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error deleting customer group: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف مجموعة العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get customers by group.
     *
     * @param CustomerGroup $group
     * @param array $data
     * @return array
     */
    public function getCustomersByGroup(CustomerGroup $group, array $data = [])
    {
        try {
            $query = $group->users();

            $customers = $query->paginate($data['per_page'] ?? 10);

            return [
                'success' => true,
                'data' => [
                    'customers' => $customers,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customers by group: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على العملاء حسب المجموعة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add customers to group.
     *
     * @param CustomerGroup $group
     * @param array $customerIds
     * @return array
     */
    public function addCustomersToGroup(CustomerGroup $group, array $customerIds)
    {
        try {
            $group->users()->attach($customerIds);

            return [
                'success' => true,
                'group_id' => $group->id,
                'message' => 'تمت إضافة العملاء إلى المجموعة بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error adding customers to group: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة العملاء إلى المجموعة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Remove customers from group.
     *
     * @param CustomerGroup $group
     * @param array $customerIds
     * @return array
     */
    public function removeCustomersFromGroup(CustomerGroup $group, array $customerIds)
    {
        try {
            $group->users()->detach($customerIds);

            return [
                'success' => true,
                'group_id' => $group->id,
                'message' => 'تمت إزالة العملاء من المجموعة بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error removing customers from group: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إزالة العملاء من المجموعة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get customer analytics.
     *
     * @param array $data
     * @return array
     */
    public function getCustomerAnalytics(array $data = [])
    {
        try {
            $startDate = $data['start_date'] ?? Carbon::now()->subMonth()->toDateString();
            $endDate = $data['end_date'] ?? Carbon::now()->toDateString();

            // Get customer count
            $customerCount = User::where('role', 'customer')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->count();

            // Get new customers by month
            $newCustomersByMonth = User::where('role', 'customer')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get customer status distribution
            $customerStatusDistribution = User::where('role', 'customer')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();

            // Get top customers by spending
            $topCustomersBySpending = User::where('role', 'customer')
                ->withCount(['orders' => function($query) {
                    $query->where('status', '!=', 'cancelled');
                }])
                ->withSum(['orders' => function($query) {
                    $query->where('status', '!=', 'cancelled');
                }], 'total_amount')
                ->orderBy('orders_sum_total_amount', 'desc')
                ->take(10)
                ->get();

            return [
                'success' => true,
                'data' => [
                    'customer_count' => $customerCount,
                    'new_customers_by_month' => $newCustomersByMonth,
                    'customer_status_distribution' => $customerStatusDistribution,
                    'top_customers_by_spending' => $topCustomersBySpending,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customer analytics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على تحليلات العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search customers.
     *
     * @param string $query
     * @param array $data
     * @return array
     */
    public function searchCustomers($query, array $data = [])
    {
        try {
            $customers = User::where('role', 'customer')
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                      ->orWhere('email', 'like', '%' . $query . '%')
                      ->orWhere('phone', 'like', '%' . $query . '%');
                })
                ->with(['customerGroups', 'orders' => function($q) {
                    $q->where('status', '!=', 'cancelled');
                }])
                ->paginate($data['per_page'] ?? 10);

            return [
                'success' => true,
                'data' => [
                    'customers' => $customers,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error searching customers: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث عن العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Export customers.
     *
     * @param array $data
     * @return array
     */
    public function exportCustomers(array $data = [])
    {
        try {
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? null;
            $status = $data['status'] ?? null;
            $group = $data['group'] ?? null;

            // Build query
            $query = User::where('role', 'customer');

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($group) {
                $query->whereHas('customerGroups', function($q) use ($group) {
                    $q->where('customer_groups.id', $group);
                });
            }

            $customers = $query->get();

            // Prepare CSV data
            $csvData = [];

            // Add headers
            $csvData[] = [
                'ID',
                'الاسم',
                'البريد الإلكتروني',
                'الهاتف',
                'الحالة',
                'تاريخ التسجيل',
                'عدد الطلبات',
                'إجمالي الإنفاق',
                'المجموعات',
            ];

            // Add customer data
            foreach ($customers as $customer) {
                $csvData[] = [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone ?? '',
                    $customer->status,
                    $customer->created_at->format('Y-m-d H:i:s'),
                    $customer->orders()->where('status', '!=', 'cancelled')->count(),
                    $customer->orders()->where('status', '!=', 'cancelled')->sum('total_amount'),
                    $customer->customerGroups->pluck('name')->implode(', '),
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'csv_data' => $csvData,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error exporting customers: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تصدير العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }
}
