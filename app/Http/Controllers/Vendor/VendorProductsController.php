
<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Image;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorProductsController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $query = Product::where('vendor_id', $vendor->id);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('category')) {
            $categoryId = $request->input('category');
            $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        $products = $query->latest()->paginate(20);

        $categories = Category::active()->get();

        return view('vendor.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::active()->get();
        return view('vendor.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'short_description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'track_quantity' => ['required', 'boolean'],
            'manage_inventory' => ['required', 'boolean'],
            'allow_backorder' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'featured' => ['required', 'boolean'],
            'new_arrival' => ['required', 'boolean'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['exists:categories,id'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:2048'],
        ]);

        // Create product
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'short_description' => $request->short_description,
            'sku' => $request->sku,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'cost' => $request->cost,
            'quantity' => $request->quantity,
            'track_quantity' => $request->track_quantity,
            'manage_inventory' => $request->manage_inventory,
            'allow_backorder' => $request->allow_backorder,
            'is_active' => $request->is_active,
            'featured' => $request->featured,
            'new_arrival' => $request->new_arrival,
        ]);

        // Attach categories
        $product->categories()->attach($request->categories);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('product-images', 'public');

                $imageData = [
                    'imageable_id' => $product->id,
                    'imageable_type' => Product::class,
                    'path' => $path,
                    'alt_text' => $request->input('alt_texts.' . $index, $product->name),
                    'is_featured' => $index === 0, // First image is featured
                    'display_order' => $index,
                ];

                Image::create($imageData);
            }
        }

        return redirect()->route('vendor.products.index')
            ->with('success', 'تم إنشاء المنتج بنجاح');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Ensure the product belongs to the vendor
        if ($product->vendor_id !== Auth::user()->vendor->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا المنتج');
        }

        $product->load(['categories', 'images', 'variants']);

        return view('vendor.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Ensure the product belongs to the vendor
        if ($product->vendor_id !== Auth::user()->vendor->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا المنتج');
        }

        $categories = Category::active()->get();
        $selectedCategories = $product->categories->pluck('id')->toArray();

        return view('vendor.products.edit', compact('product', 'categories', 'selectedCategories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Ensure the product belongs to the vendor
        if ($product->vendor_id !== Auth::user()->vendor->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا المنتج');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'short_description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'track_quantity' => ['required', 'boolean'],
            'manage_inventory' => ['required', 'boolean'],
            'allow_backorder' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'featured' => ['required', 'boolean'],
            'new_arrival' => ['required', 'boolean'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['exists:categories,id'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:2048'],
        ]);

        // Update product
        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'short_description' => $request->short_description,
            'sku' => $request->sku,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'cost' => $request->cost,
            'quantity' => $request->quantity,
            'track_quantity' => $request->track_quantity,
            'manage_inventory' => $request->manage_inventory,
            'allow_backorder' => $request->allow_backorder,
            'is_active' => $request->is_active,
            'featured' => $request->featured,
            'new_arrival' => $request->new_arrival,
        ]);

        // Update categories
        $product->categories()->sync($request->categories);

        // Handle image uploads
        if ($request->hasFile('images')) {
            // Delete existing images
            foreach ($product->images as $image) {
                Storage::delete('public/' . $image->path);
                $image->delete();
            }

            // Upload new images
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('product-images', 'public');

                $imageData = [
                    'imageable_id' => $product->id,
                    'imageable_type' => Product::class,
                    'path' => $path,
                    'alt_text' => $request->input('alt_texts.' . $index, $product->name),
                    'is_featured' => $index === 0, // First image is featured
                    'display_order' => $index,
                ];

                Image::create($imageData);
            }
        }

        return redirect()->route('vendor.products.index')
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Ensure the product belongs to the vendor
        if ($product->vendor_id !== Auth::user()->vendor->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا المنتج');
        }

        // Delete images
        foreach ($product->images as $image) {
            Storage::delete('public/' . $image->path);
            $image->delete();
        }

        // Delete product
        $product->delete();

        return redirect()->route('vendor.products.index')
            ->with('success', 'تم حذف المنتج بنجاح');
    }

    /**
     * Upload product image.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('image')->store('product-images', 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Delete product image.
     */
    public function deleteImage($imageId)
    {
        $image = Image::findOrFail($imageId);

        // Ensure the image belongs to a product owned by the vendor
        if ($image->imageable_type !== Product::class || 
            $image->imageable->vendor_id !== Auth::user()->vendor->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصورة');
        }

        Storage::delete('public/' . $image->path);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصورة بنجاح',
        ]);
    }
}
