<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\VendorPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminVendorController extends Controller
{
    /**
     * Display a listing of vendors.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vendors = Vendor::with(['user', 'products' => function($query) {
            $query->active()->count();
        }])
            ->latest()
            ->paginate(20);

        return view('admin.vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.vendors.create');
    }

    /**
     * Store a newly created vendor in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'in:pending,active,suspended'],
        ]);

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Assign vendor role
            $user->assignRole('vendor');

            // Create vendor
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'slug' => \Illuminate\Support\Str::slug($request->name),
                'status' => $request->status,
                'commission_rate' => $request->commission_rate,
            ]);

            DB::commit();

            return redirect()->route('admin.vendors.index')
                ->with('success', 'تم إنشاء بائع جديد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء البائع: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified vendor.
     *
     * @param \App\Models\Vendor $vendor
     * @return \Illuminate\Http\Response
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['user', 'products', 'orders']);

        // Calculate statistics
        $totalProducts = $vendor->products()->count();
        $totalOrders = $vendor->orders()->count();
        $totalRevenue = $vendor->orders()->sum('total_amount');
        $pendingOrders = $vendor->orders()->where('status', 'pending')->count();
        $processingOrders = $vendor->orders()->where('status', 'processing')->count();
        $completedOrders = $vendor->orders()->where('status', 'delivered')->count();
        $totalReviews = $vendor->reviews()->count();
        $averageRating = $vendor->reviews()->avg('rating') ?? 0;
        $walletBalance = $vendor->wallet_balance;
        $totalPayouts = VendorPayout::where('vendor_id', $vendor->id)->sum('amount');

        return view('admin.vendors.show', compact(
            'vendor',
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'totalReviews',
            'averageRating',
            'walletBalance',
            'totalPayouts'
        ));
    }

    /**
     * Show the form for editing the specified vendor.
     *
     * @param \App\Models\Vendor $vendor
     * @return \Illuminate\Http\Response
     */
    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified vendor in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Vendor $vendor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('vendors')->ignore($vendor->id)],
            'status' => ['required', 'string', 'in:pending,active,suspended'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url'],
        ]);

        try {
            DB::beginTransaction();

            // Update vendor
            $vendor->update([
                'name' => $request->name,
                'status' => $request->status,
                'commission_rate' => $request->commission_rate,
                'bank_account_name' => $request->bank_account_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_name' => $request->bank_name,
                'bank_iban' => $request->bank_iban,
                'tax_number' => $request->tax_number,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
            ]);

            // Update user email if provided
            if ($request->filled('email')) {
                $vendor->user->email = $request->email;
                $vendor->user->save();
            }

            DB::commit();

            return redirect()->route('admin.vendors.index')
                ->with('success', 'تم تحديث معلومات البائع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث البائع: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified vendor from storage.
     *
     * @param \App\Models\Vendor $vendor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vendor $vendor)
    {
        try {
            DB::beginTransaction();

            // Don't allow deletion of vendors with active orders or products
            if ($vendor->products()->exists() || $vendor->orders()->exists()) {
                return redirect()->back()
                    ->with('error', 'لا يمكن حذف هذا البائع لأن لديه منتجات أو طلبات');
            }

            // Delete logo and cover image if they exist
            if ($vendor->logo) {
                Storage::disk('public')->delete($vendor->logo);
            }

            if ($vendor->cover_image) {
                Storage::disk('public')->delete($vendor->cover_image);
            }

            // Delete the user account
            $vendor->user->delete();

            // Delete vendor
            $vendor->delete();

            DB::commit();

            return redirect()->route('admin.vendors.index')
                ->with('success', 'تم حذف البائع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء حذف البائع: ' . $e->getMessage());
        }
    }

    /**
     * Change vendor status.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Vendor $vendor
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, Vendor $vendor)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,active,suspended'],
        ]);

        $vendor->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', 'تم تغيير حالة البائع بنجاح');
    }

    /**
     * Display vendor payouts.
     *
     * @param \App\Models\Vendor $vendor
     * @return \Illuminate\Http\Response
     */
    public function payouts(Vendor $vendor)
    {
        $payouts = VendorPayout::where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('admin.vendors.payouts', compact('vendor', 'payouts'));
    }

    /**
     * Approve vendor payout.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\VendorPayout $payout
     * @return \Illuminate\Http\Response
     */
    public function approvePayout(Request $request, VendorPayout $payout)
    {
        $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Update payout status
            $payout->update([
                'payout_status' => 'completed',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'تم الموافقة على الدفعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء الموافقة على الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Reject vendor payout.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\VendorPayout $payout
     * @return \Illuminate\Http\Response
     */
    public function rejectPayout(Request $request, VendorPayout $payout)
    {
        $request->validate([
            'notes' => ['required', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Update payout status
            $payout->update([
                'payout_status' => 'rejected',
                'notes' => $request->notes,
            ]);

            // Refund amount to wallet
            $payout->vendor->increment('wallet_balance', $payout->amount);

            DB::commit();

            return redirect()->back()
                ->with('success', 'تم رفض الدفعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء رفض الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Display vendor analytics.
     *
     * @param \App\Models\Vendor $vendor
     * @return \Illuminate\Http\Response
     */
    public function analytics(Vendor $vendor)
    {
        // Get sales data for the last 30 days
        $salesData = $vendor->orders()
            ->where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_sales, COUNT(*) as total_orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get top selling products
        $topProducts = $vendor->products()
            ->withCount(['orderItems' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'delivered');
                });
            }])
            ->withSum(['orderItems' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'delivered');
                });
            }], 'quantity')
            ->orderBy('order_items_sum_quantity', 'desc')
            ->take(10)
            ->get();

        // Get product category distribution
        $categoryDistribution = $vendor->products()
            ->join('product_category', 'products.id', '=', 'product_category.product_id')
            ->join('categories', 'product_category.category_id', '=', 'categories.id')
            ->select('categories.name as category', \DB::raw('COUNT(*) as product_count'))
            ->groupBy('categories.name')
            ->get();

        return view('admin.vendors.analytics', compact('vendor', 'salesData', 'topProducts', 'categoryDistribution'));
    }
}
