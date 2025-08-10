<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    /**
     * Display a listing of the vendors.
     */
    public function index()
    {
        $vendors = Vendor::with(['user', 'products' => function($query) {
            $query->active()->count();
        }])
            ->latest()
            ->paginate(20);

        return view('vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create()
    {
        return view('admin.vendors.create');
    }

    /**
     * Store a newly created vendor in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vendors'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign vendor role
        $user->roles()->attach(config('roles.vendor_id'));

        // Create vendor
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => 'active',
            'commission_rate' => $request->commission_rate,
        ]);

        return redirect()->route('admin.vendors.index')
            ->with('success', 'تم إنشاء بائع جديد بنجاح');
    }

    /**
     * Display the specified vendor.
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

        return view('admin.vendors.show', compact(
            'vendor', 
            'totalProducts', 
            'totalOrders', 
            'totalRevenue',
            'pendingOrders',
            'processingOrders',
            'completedOrders'
        ));
    }

    /**
     * Show the form for editing the specified vendor.
     */
    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified vendor in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('vendors')->ignore($vendor->id)],
            'status' => ['required', 'string', 'in:pending,active,suspended'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url'],
        ]);

        $vendor->update($request->all());

        // Update user email if provided
        if ($request->filled('email')) {
            $vendor->user->email = $request->email;
            $vendor->user->save();
        }

        return redirect()->route('admin.vendors.index')
            ->with('success', 'تم تحديث معلومات البائع بنجاح');
    }

    /**
     * Remove the specified vendor from storage.
     */
    public function destroy(Vendor $vendor)
    {
        // Don't allow deletion of vendors with active orders or products
        if ($vendor->products()->exists() || $vendor->orders()->exists()) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف هذا البائع لأن لديه منتجات أو طلبات');
        }

        // Delete the user account
        $vendor->user->delete();

        return redirect()->route('admin.vendors.index')
            ->with('success', 'تم حذف البائع بنجاح');
    }

    /**
     * Change vendor status.
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
     * Display vendor storefront.
     */
    public function storefront($slug)
    {
        $vendor = Vendor::with(['products' => function($query) {
            $query->active()->latest()->take(12);
        }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Get vendor statistics
        $stats = [
            'total_products' => $vendor->products()->active()->count(),
            'average_rating' => $vendor->average_rating,
            'total_reviews' => $vendor->total_reviews,
        ];

        return view('vendor.storefront', compact('vendor', 'stats'));
    }

    /**
     * Show vendor dashboard.
     */
    public function dashboard()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.profile.create');
        }

        // Get vendor statistics
        $stats = [
            'total_products' => $vendor->products()->active()->count(),
            'total_orders' => $vendor->orders()->count(),
            'total_reviews' => $vendor->total_reviews,
            'average_rating' => $vendor->average_rating,
            'total_earnings' => $vendor->total_earnings,
            'wallet_balance' => $vendor->wallet_balance,
            'pending_payouts' => $vendor->pending_payouts,
        ];

        // Get recent orders
        $recentOrders = $vendor->orders()->latest()->take(10)->get();

        // Get low stock products
        $lowStockProducts = $vendor->products()
            ->where('manage_inventory', true)
            ->where('quantity', '<=', 10)
            ->latest()
            ->take(5)
            ->get();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentOrders', 'lowStockProducts'));
    }

    /**
     * Show vendor profile creation form.
     */
    public function createProfile()
    {
        return view('vendor.profile.create');
    }

    /**
     * Store vendor profile.
     */
    public function storeProfile(Request $request)
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.profile.create');
        }

        $request->validate([
            'bio' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        try {
            // Handle logo upload
            $logoPath = $vendor->logo;
            if ($request->hasFile('logo')) {
                if ($vendor->logo) {
                    Storage::disk('public')->delete($vendor->logo);
                }

                $logoPath = $request->file('logo')->store('vendors/logos', 'public');
            }

            // Handle cover image upload
            $coverImagePath = $vendor->cover_image;
            if ($request->hasFile('cover_image')) {
                if ($vendor->cover_image) {
                    Storage::disk('public')->delete($vendor->cover_image);
                }

                $coverImagePath = $request->file('cover_image')->store('vendors/cover-images', 'public');
            }

            // Update vendor
            $vendor->update([
                'bio' => $request->bio,
                'logo' => $logoPath,
                'cover_image' => $coverImagePath,
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

            return redirect()->route('vendor.dashboard')
                ->with('success', 'تم تحمليل ملف التعريف بنجاح');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث ملف التعريف: ' . $e->getMessage());
        }
    }

    /**
     * Show vendor payouts.
     */
    public function payouts()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.profile.create');
        }

        $payouts = $vendor->payouts()->latest()->paginate(20);

        return view('vendor.payouts', compact('vendor', 'payouts'));
    }

    /**
     * Request a new payout.
     */
    public function requestPayout(Request $request)
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.profile.create');
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        // Check if vendor has enough balance
        if ($vendor->wallet_balance < $request->amount) {
            return back()->with('error', 'رصيدك غير كافي للسحب');
        }

        // Create payout
        $payout = $vendor->payouts()->create([
            'amount' => $request->amount,
            'payout_method' => 'bank_transfer',
            'payout_status' => 'pending',
            'notes' => $request->notes,
        ]);

        // Deduct amount from wallet
        $vendor->decrement('wallet_balance', $request->amount);

        return redirect()->route('vendor.payouts')
            ->with('success', 'تم طلب السحب بنجاح');
    }
}
