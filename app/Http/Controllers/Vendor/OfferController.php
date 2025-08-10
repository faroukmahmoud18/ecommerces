<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    /**
     * Display a listing of the offers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vendor = auth()->user()->vendor;

        $offers = Offer::with(['products', 'categories', 'vendors'])
            ->whereHas('vendors', function($query) use ($vendor) {
                $query->where('id', $vendor->id);
            })
            ->latest()
            ->paginate(20);

        return view('vendor.offers.index', compact('offers'));
    }

    /**
     * Show the form for creating a new offer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $vendor = auth()->user()->vendor;

        $products = Product::where('vendor_id', $vendor->id)
            ->active()
            ->pluck('name', 'id');

        $categories = Category::active()->pluck('name', 'id');

        return view('vendor.offers.create', compact('products', 'categories'));
    }

    /**
     * Store a newly created offer in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'in:active,inactive,expired'],
            'image' => ['nullable', 'image', 'max:2048'],
            'products' => ['nullable', 'array'],
            'products.*' => ['exists:products,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        try {
            DB::beginTransaction();

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('offers', 'public');
            }

            // Create offer
            $offer = Offer::create([
                'name' => $request->name,
                'description' => $request->description,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'image' => $imagePath,
            ]);

            // Attach products
            if ($request->filled('products')) {
                $offer->products()->attach($request->products);
            }

            // Attach categories
            if ($request->filled('categories')) {
                $offer->categories()->attach($request->categories);
            }

            // Attach current vendor
            $offer->vendors()->attach($vendor->id);

            DB::commit();

            return redirect()->route('vendor.offers.index')
                ->with('success', 'تم إنشاء العرض بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء العرض: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified offer.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vendor = auth()->user()->vendor;

        $offer = Offer::with(['products', 'categories', 'vendors'])
            ->whereHas('vendors', function($query) use ($vendor) {
                $query->where('id', $vendor->id);
            })
            ->findOrFail($id);

        return view('vendor.offers.show', compact('offer'));
    }

    /**
     * Show the form for editing the specified offer.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vendor = auth()->user()->vendor;

        $offer = Offer::with(['products', 'categories', 'vendors'])
            ->whereHas('vendors', function($query) use ($vendor) {
                $query->where('id', $vendor->id);
            })
            ->findOrFail($id);

        $products = Product::where('vendor_id', $vendor->id)
            ->active()
            ->pluck('name', 'id');

        $categories = Category::active()->pluck('name', 'id');

        return view('vendor.offers.edit', compact('offer', 'products', 'categories'));
    }

    /**
     * Update the specified offer in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $vendor = auth()->user()->vendor;

        $offer = Offer::whereHas('vendors', function($query) use ($vendor) {
                $query->where('id', $vendor->id);
            })
            ->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'in:active,inactive,expired'],
            'image' => ['nullable', 'image', 'max:2048'],
            'products' => ['nullable', 'array'],
            'products.*' => ['exists:products,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        try {
            DB::beginTransaction();

            // Handle image upload
            $imagePath = $offer->image;
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($offer->image) {
                    Storage::disk('public')->delete($offer->image);
                }

                $imagePath = $request->file('image')->store('offers', 'public');
            }

            // Update offer
            $offer->update([
                'name' => $request->name,
                'description' => $request->description,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'image' => $imagePath,
            ]);

            // Sync products
            if ($request->filled('products')) {
                $offer->products()->sync($request->products);
            } else {
                $offer->products()->detach();
            }

            // Sync categories
            if ($request->filled('categories')) {
                $offer->categories()->sync($request->categories);
            } else {
                $offer->categories()->detach();
            }

            // Ensure current vendor is still attached
            if (!$offer->vendors()->where('id', $vendor->id)->exists()) {
                $offer->vendors()->attach($vendor->id);
            }

            DB::commit();

            return redirect()->route('vendor.offers.index')
                ->with('success', 'تم تحديث العرض بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث العرض: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified offer from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $vendor = auth()->user()->vendor;

        $offer = Offer::whereHas('vendors', function($query) use ($vendor) {
                $query->where('id', $vendor->id);
            })
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($offer->image) {
                Storage::disk('public')->delete($offer->image);
            }

            // Detach all relationships
            $offer->products()->detach();
            $offer->categories()->detach();

            // Detach vendors but only if this is the only vendor
            if ($offer->vendors()->count() === 1) {
                $offer->vendors()->detach();
                // Delete offer if no vendors left
                $offer->delete();
            } else {
                // Just detach current vendor
                $offer->vendors()->detach($vendor->id);
            }

            DB::commit();

            return redirect()->route('vendor.offers.index')
                ->with('success', 'تم حذف العرض بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء حذف العرض: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of the specified offer.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus($id)
    {
        $vendor = auth()->user()->vendor;

        $offer = Offer::whereHas('vendors', function($query) use ($vendor) {
                $query->where('id', $vendor->id);
            })
            ->findOrFail($id);

        $offer->status = $offer->status === 'active' ? 'inactive' : 'active';
        $offer->save();

        return back()->with('success', 'تم تغيير حالة العرض بنجاح');
    }
}
