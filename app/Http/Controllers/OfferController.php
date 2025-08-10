
<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;

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
            ->paginate(12);

        return view('offers.index', compact('offers'));
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

        // Check if offer is active
        if (!$offer->isActive()) {
            abort(404, 'العروض غير متاحة حالياً');
        }

        return view('offers.show', compact('offer'));
    }

    /**
     * Get active offers for a product.
     *
     * @param Product $product
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductOffers(Product $product)
    {
        return $product->offers()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Get active offers for a category.
     *
     * @param Category $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoryOffers(Category $category)
    {
        return $category->offers()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Get active offers for a vendor.
     *
     * @param Vendor $vendor
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVendorOffers(Vendor $vendor)
    {
        return $vendor->offers()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Calculate discounted price for a product.
     *
     * @param Product $product
     * @return array
     */
    public function calculateDiscount(Product $product)
    {
        $offers = $this->getProductOffers($product);
        $discount = 0;
        $discountType = null;
        $offerName = null;

        foreach ($offers as $offer) {
            if ($offer->discount_type === 'percentage') {
                $offerDiscount = $product->price * ($offer->discount_value / 100);
            } else {
                $offerDiscount = $offer->discount_value;
            }

            if ($offerDiscount > $discount) {
                $discount = $offerDiscount;
                $discountType = $offer->discount_type;
                $offerName = $offer->name;
            }
        }

        return [
            'has_discount' => $discount > 0,
            'discount' => $discount,
            'discount_type' => $discountType,
            'offer_name' => $offerName,
            'original_price' => $product->price,
            'discounted_price' => $product->price - $discount,
        ];
    }
}
