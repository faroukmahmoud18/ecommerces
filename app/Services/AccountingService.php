
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Vendor;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\Commission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AccountingService
{
    /**
     * Calculate vendor commission for a period.
     *
     * @param Vendor $vendor
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    public function calculateVendorCommission(Vendor $vendor, $period = 'month')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            // Get vendor's orders
            $orders = Order::where('vendor_id', $vendor->id)
                ->where('created_at', '>=', $startDate)
                ->where('status', 'delivered')
                ->get();

            // Calculate commission
            $totalSales = $orders->sum('total_amount');
            $commissionRate = $vendor->commission_rate / 100;
            $totalCommission = $totalSales * $commissionRate;

            // Calculate platform fees
            $platformFeeRate = config('app.platform_fee_rate', 0.05);
            $platformFees = $totalSales * $platformFeeRate;

            // Calculate shipping fees
            $shippingFees = $orders->sum('shipping_cost');

            // Calculate tax
            $taxAmount = $orders->sum('tax_amount');

            // Calculate net amount (vendor's share)
            $netAmount = $totalSales - $platformFees - $shippingFees - $taxAmount;

            // Get existing payouts for the period
            $payouts = Payout::where('vendor_id', $vendor->id)
                ->where('created_at', '>=', $startDate)
                ->get();

            $totalPayouts = $payouts->sum('amount');

            // Calculate pending payout amount
            $pendingPayout = $netAmount - $totalPayouts;

            return [
                'success' => true,
                'data' => [
                    'vendor_id' => $vendor->id,
                    'vendor_name' => $vendor->name,
                    'period' => $period,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => now()->format('Y-m-d'),
                    'total_sales' => $totalSales,
                    'commission_rate' => $vendor->commission_rate,
                    'total_commission' => $totalCommission,
                    'platform_fees' => $platformFees,
                    'shipping_fees' => $shippingFees,
                    'tax_amount' => $taxAmount,
                    'net_amount' => $netAmount,
                    'total_payouts' => $totalPayouts,
                    'pending_payout' => $pendingPayout,
                    'orders_count' => $orders->count(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating vendor commission: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب عمولة البائع',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a payout for a vendor.
     *
     * @param Vendor $vendor
     * @param array $data
     * @return array
     */
    public function createPayout(Vendor $vendor, array $data)
    {
        DB::beginTransaction();

        try {
            // Calculate commission for the vendor
            $commissionResult = $this->calculateVendorCommission($vendor);

            if (!$commissionResult['success']) {
                return $commissionResult;
            }

            $commissionData = $commissionResult['data'];

            // Check if there's a pending payout
            if ($commissionData['pending_payout'] <= 0) {
                return [
                    'success' => false,
                    'message' => 'لا توجد دفعة مستحقة للبائع',
                ];
            }

            // Create payout
            $payout = Payout::create([
                'vendor_id' => $vendor->id,
                'amount' => $commissionData['pending_payout'],
                'payment_method' => $data['payment_method'],
                'payment_details' => $data['payment_details'] ?? null,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // Create transactions
            $this->createTransactions($vendor, $payout, $commissionData);

            // Update vendor balance
            $vendor->update([
                'balance' => $vendor->balance - $commissionData['pending_payout'],
            ]);

            DB::commit();

            return [
                'success' => true,
                'payout_id' => $payout->id,
                'message' => 'تم إنشاء الدفعة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating payout: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الدفعة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create accounting transactions for a payout.
     *
     * @param Vendor $vendor
     * @param Payout $payout
     * @param array $commissionData
     */
    private function createTransactions(Vendor $vendor, Payout $payout, $commissionData)
    {
        // Create commission transaction
        Commission::create([
            'vendor_id' => $vendor->id,
            'payout_id' => $payout->id,
            'order_id' => null,
            'amount' => $commissionData['total_commission'],
            'type' => 'commission',
            'description' => 'عمولة مبيعات الفترة: ' . $commissionData['period'],
            'status' => 'completed',
        ]);

        // Create platform fee transaction
        Transaction::create([
            'vendor_id' => $vendor->id,
            'payout_id' => $payout->id,
            'amount' => $commissionData['platform_fees'],
            'type' => 'fee',
            'description' => 'رسوم منصة للفترة: ' . $commissionData['period'],
            'status' => 'completed',
        ]);

        // Create shipping fee transaction
        Transaction::create([
            'vendor_id' => $vendor->id,
            'payout_id' => $payout->id,
            'amount' => $commissionData['shipping_fees'],
            'type' => 'shipping',
            'description' => 'تكاليف الشحن للفترة: ' . $commissionData['period'],
            'status' => 'completed',
        ]);

        // Create tax transaction
        Transaction::create([
            'vendor_id' => $vendor->id,
            'payout_id' => $payout->id,
            'amount' => $commissionData['tax_amount'],
            'type' => 'tax',
            'description' => 'الضريبة للفترة: ' . $commissionData['period'],
            'status' => 'completed',
        ]);

        // Create payout transaction
        Transaction::create([
            'vendor_id' => $vendor->id,
            'payout_id' => $payout->id,
            'amount' => $payout->amount,
            'type' => 'payout',
            'description' => 'دفعة للبائع',
            'status' => 'pending',
        ]);
    }

    /**
     * Get vendor financial statements.
     *
     * @param Vendor $vendor
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getVendorFinancialStatements(Vendor $vendor, $startDate, $endDate)
    {
        try {
            // Get orders in the date range
            $orders = Order::where('vendor_id', $vendor->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'delivered')
                ->get();

            // Calculate totals
            $totalSales = $orders->sum('total_amount');
            $totalCommission = $totalSales * ($vendor->commission_rate / 100);
            $platformFees = $totalSales * config('app.platform_fee_rate', 0.05);
            $shippingFees = $orders->sum('shipping_cost');
            $taxAmount = $orders->sum('tax_amount');
            $netAmount = $totalSales - $platformFees - $shippingFees - $taxAmount;

            // Get payouts in the date range
            $payouts = Payout::where('vendor_id', $vendor->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalPayouts = $payouts->sum('amount');

            // Get transactions in the date range
            $transactions = Transaction::where('vendor_id', $vendor->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Group transactions by type
            $transactionTypes = $transactions->groupBy('type');

            return [
                'success' => true,
                'data' => [
                    'vendor_id' => $vendor->id,
                    'vendor_name' => $vendor->name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'sales_summary' => [
                        'total_sales' => $totalSales,
                        'total_commission' => $totalCommission,
                        'platform_fees' => $platformFees,
                        'shipping_fees' => $shippingFees,
                        'tax_amount' => $taxAmount,
                        'net_amount' => $netAmount,
                    ],
                    'payout_summary' => [
                        'total_payouts' => $totalPayouts,
                        'pending_payout' => $netAmount - $totalPayouts,
                    ],
                    'transaction_summary' => [
                        'commissions' => $transactionTypes->get('commission', collect())->sum('amount'),
                        'fees' => $transactionTypes->get('fee', collect())->sum('amount'),
                        'shipping' => $transactionTypes->get('shipping', collect())->sum('amount'),
                        'tax' => $transactionTypes->get('tax', collect())->sum('amount'),
                        'payouts' => $transactionTypes->get('payout', collect())->sum('amount'),
                    ],
                    'orders_count' => $orders->count(),
                    'payouts_count' => $payouts->count(),
                    'transactions_count' => $transactions->count(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting vendor financial statements: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على البيانات المالية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get platform financial statements.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getPlatformFinancialStatements($startDate, $endDate)
    {
        try {
            // Get orders in the date range
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'delivered')
                ->get();

            // Calculate totals
            $totalSales = $orders->sum('total_amount');
            $platformFees = $totalSales * config('app.platform_fee_rate', 0.05);
            $shippingFees = $orders->sum('shipping_cost');
            $taxAmount = $orders->sum('tax_amount');

            // Get payouts in the date range
            $payouts = Payout::whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalPayouts = $payouts->sum('amount');

            // Get transactions in the date range
            $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Group transactions by type
            $transactionTypes = $transactions->groupBy('type');

            // Get vendor commissions
            $vendorCommissions = $transactions->where('type', 'commission')->sum('amount');

            // Calculate platform profit
            $platformProfit = $platformFees + $shippingFees + $taxAmount - $totalPayouts;

            return [
                'success' => true,
                'data' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'sales_summary' => [
                        'total_sales' => $totalSales,
                        'platform_fees' => $platformFees,
                        'shipping_fees' => $shippingFees,
                        'tax_amount' => $taxAmount,
                        'platform_profit' => $platformProfit,
                    ],
                    'payout_summary' => [
                        'total_payouts' => $totalPayouts,
                    ],
                    'transaction_summary' => [
                        'vendor_commissions' => $vendorCommissions,
                        'platform_fees' => $transactionTypes->get('fee', collect())->sum('amount'),
                        'shipping' => $transactionTypes->get('shipping', collect())->sum('amount'),
                        'tax' => $transactionTypes->get('tax', collect())->sum('amount'),
                        'payouts' => $transactionTypes->get('payout', collect())->sum('amount'),
                    ],
                    'orders_count' => $orders->count(),
                    'payouts_count' => $payouts->count(),
                    'transactions_count' => $transactions->count(),
                    'vendors_count' => Vendor::where('status', 'approved')->count(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting platform financial statements: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على البيانات المالية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get vendor balance.
     *
     * @param Vendor $vendor
     * @return float
     */
    public function getVendorBalance(Vendor $vendor)
    {
        try {
            // Calculate total sales for the vendor
            $totalSales = Order::where('vendor_id', $vendor->id)
                ->where('status', 'delivered')
                ->sum('total_amount');

            // Calculate total payouts for the vendor
            $totalPayouts = Payout::where('vendor_id', $vendor->id)
                ->sum('amount');

            // Calculate balance
            $balance = $totalSales - $totalPayouts;

            return $balance;
        } catch (\Exception $e) {
            Log::error('Error getting vendor balance: ' . $e->getMessage());

            return 0;
        }
    }
}
