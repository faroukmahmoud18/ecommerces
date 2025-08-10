<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
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
        $offers = Offer::with(['products', 'categories', 'vendors'])
            ->latest()
            ->paginate(20);

        return view('admin.offers.index', compact('offers'));
    }

    /**
     * Show the form for creating a new offer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::active()->pluck('name', 'id');
        $categories = Category::active()->pluck('name', 'id');
        $vendors = Vendor::active()->pluck('name', 'id');

        return view('admin.offers.create', compact('products', 'categories', 'vendors'));
    }

    /**
     * Store a newly created offer in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
            'vendors' => ['nullable', 'array'],
            'vendors.*' => ['exists:vendors,id'],
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

            // Attach vendors
            if ($request->filled('vendors')) {
                $offer->vendors()->attach($request->vendors);
            }

            DB::commit();

            return redirect()->route('admin.offers.index')
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
        $offer = Offer::with(['products', 'categories', 'vendors'])
            ->findOrFail($id);

        return view('admin.offers.show', compact('offer'));
    }

    /**
     * Show the form for editing the specified offer.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $offer = Offer::with(['products', 'categories', 'vendors'])
            ->findOrFail($id);

        $products = Product::active()->pluck('name', 'id');
        $categories = Category::active()->pluck('name', 'id');
        $vendors = Vendor::active()->pluck('name', 'id');

        return view('admin.offers.edit', compact('offer', 'products', 'categories', 'vendors'));
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
        $offer = Offer::findOrFail($id);

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
            'vendors' => ['nullable', 'array'],
            'vendors.*' => ['exists:vendors,id'],
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

            // Sync vendors
            if ($request->filled('vendors')) {
                $offer->vendors()->sync($request->vendors);
            } else {
                $offer->vendors()->detach();
            }

            DB::commit();

            return redirect()->route('admin.offers.index')
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
        $offer = Offer::findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($offer->image) {
                Storage::disk('public')->delete($offer->image);
            }

            // Detach all relationships
            $offer->products()->detach();
            $offer->categories()->detach();
            $offer->vendors()->detach();

            // Delete offer
            $offer->delete();

            DB::commit();

            return redirect()->route('admin.offers.index')
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
        $offer = Offer::findOrFail($id);

        $offer->status = $offer->status === 'active' ? 'inactive' : 'active';
        $offer->save();

        return back()->with('success', 'تم تغيير حالة العرض بنجاح');
    }
}
