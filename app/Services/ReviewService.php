
<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\ReviewImage;
use App\Models\ReviewResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReviewService
{
    /**
     * Create a new review.
     *
     * @param array $data
     * @param User $user
     * @return array
     */
    public function createReview(array $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check if user has purchased the product
            $hasPurchased = Order::where('customer_id', $user->id)
                ->whereHas('items', function($q) use ($data) {
                    $q->where('product_id', $data['product_id']);
                })
                ->exists();

            if (!$hasPurchased) {
                return [
                    'success' => false,
                    'message' => 'لا يمكنك مراجعة هذا المنتج لأنك لم تشتره',
                ];
            }

            // Check if user has already reviewed this product
            $existingReview = Review::where('user_id', $user->id)
                ->where('product_id', $data['product_id'])
                ->first();

            if ($existingReview) {
                return [
                    'success' => false,
                    'message' => 'لقد قمت بمراجعة هذا المنتج من قبل',
                ];
            }

            // Create review
            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $data['product_id'],
                'vendor_id' => $data['vendor_id'],
                'order_id' => $data['order_id'],
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'comment' => $data['comment'] ?? null,
                'status' => 'pending',
                'verified_purchase' => true,
            ]);

            // Upload review images if any
            if (!empty($data['images'])) {
                foreach ($data['images'] as $image) {
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'path' => $image['path'],
                        'alt_text' => $image['alt_text'] ?? null,
                    ]);
                }
            }

            // Update product rating
            $this->updateProductRating($data['product_id']);

            // Update vendor rating
            $this->updateVendorRating($data['vendor_id']);

            DB::commit();

            return [
                'success' => true,
                'review_id' => $review->id,
                'message' => 'تمت إضافة المراجعة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating review: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة المراجعة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update a review.
     *
     * @param Review $review
     * @param array $data
     * @param User $user
     * @return array
     */
    public function updateReview(Review $review, array $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check if user owns the review
            if ($review->user_id !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'لا يمكنك تعديل هذه المراجعة',
                ];
            }

            // Update review
            $review->update([
                'rating' => $data['rating'],
                'title' => $data['title'] ?? $review->title,
                'comment' => $data['comment'] ?? $review->comment,
            ]);

            // Update review images if any
            if (!empty($data['images'])) {
                // Delete existing images
                $review->images()->delete();

                // Upload new images
                foreach ($data['images'] as $image) {
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'path' => $image['path'],
                        'alt_text' => $image['alt_text'] ?? null,
                    ]);
                }
            }

            // Update product rating
            $this->updateProductRating($review->product_id);

            // Update vendor rating
            $this->updateVendorRating($review->vendor_id);

            DB::commit();

            return [
                'success' => true,
                'review_id' => $review->id,
                'message' => 'تم تحديث المراجعة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating review: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث المراجعة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a review.
     *
     * @param Review $review
     * @param User $user
     * @return bool
     */
    public function deleteReview(Review $review, $user)
    {
        try {
            // Check if user owns the review or is an admin
            if ($review->user_id !== $user->id && !$user->hasRole('admin')) {
                return false;
            }

            // Delete review images
            $review->images()->delete();

            // Delete review
            $review->delete();

            // Update product rating
            $this->updateProductRating($review->product_id);

            // Update vendor rating
            $this->updateVendorRating($review->vendor_id);

            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting review: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Approve a review.
     *
     * @param Review $review
     * @return bool
     */
    public function approveReview(Review $review)
    {
        try {
            $review->update(['status' => 'approved']);

            // Notify user about approval
            $review->user->notify(new \App\Notifications\ReviewApproved($review));

            return true;
        } catch (\Exception $e) {
            Log::error('Error approving review: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Reject a review.
     *
     * @param Review $review
     * @param string $reason
     * @return bool
     */
    public function rejectReview(Review $review, $reason)
    {
        try {
            $review->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            // Notify user about rejection
            $review->user->notify(new \App\Notifications\ReviewRejected($review, $reason));

            return true;
        } catch (\Exception $e) {
            Log::error('Error rejecting review: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Add a response to a review.
     *
     * @param Review $review
     * @param string $response
     * @param User $user
     * @return ReviewResponse
     */
    public function addResponse(Review $review, $response, $user)
    {
        try {
            $reviewResponse = ReviewResponse::create([
                'review_id' => $review->id,
                'user_id' => $user->id,
                'response' => $response,
            ]);

            // Notify user about response
            $review->user->notify(new \App\Notifications\ReviewResponse($review, $reviewResponse));

            return $reviewResponse;
        } catch (\Exception $e) {
            Log::error('Error adding review response: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get product reviews.
     *
     * @param Product $product
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductReviews(Product $product, $filters = [])
    {
        $query = Review::where('product_id', $product->id)
            ->where('status', 'approved');

        // Apply rating filter
        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('comment', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply order filter
        if (!empty($filters['order'])) {
            switch ($filters['order']) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'highest':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'lowest':
                    $query->orderBy('rating', 'asc');
                    break;
                case 'helpful':
                    $query->orderBy('helpful_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->with(['user', 'images', 'responses'])->paginate(10);
    }

    /**
     * Get vendor reviews.
     *
     * @param Vendor $vendor
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVendorReviews(Vendor $vendor, $filters = [])
    {
        $query = Review::where('vendor_id', $vendor->id)
            ->where('status', 'approved');

        // Apply rating filter
        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('comment', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply order filter
        if (!empty($filters['order'])) {
            switch ($filters['order']) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'highest':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'lowest':
                    $query->orderBy('rating', 'asc');
                    break;
                case 'helpful':
                    $query->orderBy('helpful_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->with(['user', 'product', 'images', 'responses'])->paginate(10);
    }

    /**
     * Get review statistics.
     *
     * @param Product $product
     * @return array
     */
    public function getReviewStatistics(Product $product)
    {
        $reviews = Review::where('product_id', $product->id)
            ->where('status', 'approved')
            ->get();

        $totalReviews = $reviews->count();

        if ($totalReviews === 0) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => [
                    5 => 0,
                    4 => 0,
                    3 => 0,
                    2 => 0,
                    1 => 0,
                ],
            ];
        }

        $averageRating = $reviews->avg('rating');
        $ratingDistribution = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 1),
            'rating_distribution' => $ratingDistribution,
        ];
    }

    /**
     * Get review helpful count.
     *
     * @param Review $review
     * @param User $user
     * @return array
     */
    public function getReviewHelpfulCount(Review $review, $user)
    {
        $helpfulCount = $review->helpful_count;
        $userHelpful = $review->helpfulUsers()->where('user_id', $user->id)->exists();

        return [
            'helpful_count' => $helpfulCount,
            'user_helpful' => $userHelpful,
        ];
    }

    /**
     * Mark review as helpful.
     *
     * @param Review $review
     * @param User $user
     * @return bool
     */
    public function markReviewHelpful(Review $review, $user)
    {
        try {
            // Check if user has already marked this review as helpful
            if ($review->helpfulUsers()->where('user_id', $user->id)->exists()) {
                return false;
            }

            // Add user to helpful users
            $review->helpfulUsers()->attach($user->id);

            // Increment helpful count
            $review->increment('helpful_count');

            return true;
        } catch (\Exception $e) {
            Log::error('Error marking review as helpful: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Unmark review as helpful.
     *
     * @param Review $review
     * @param User $user
     * @return bool
     */
    public function unmarkReviewHelpful(Review $review, $user)
    {
        try {
            // Check if user has marked this review as helpful
            if (!$review->helpfulUsers()->where('user_id', $user->id)->exists()) {
                return false;
            }

            // Remove user from helpful users
            $review->helpfulUsers()->detach($user->id);

            // Decrement helpful count
            $review->decrement('helpful_count');

            return true;
        } catch (\Exception $e) {
            Log::error('Error unmarking review as helpful: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update product rating.
     *
     * @param int $productId
     * @return void
     */
    private function updateProductRating($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $reviews = Review::where('product_id', $productId)
            ->where('status', 'approved')
            ->get();

        if ($reviews->isEmpty()) {
            $product->update([
                'rating' => 0,
                'reviews_count' => 0,
            ]);

            return;
        }

        $averageRating = $reviews->avg('rating');
        $reviewsCount = $reviews->count();

        $product->update([
            'rating' => round($averageRating, 1),
            'reviews_count' => $reviewsCount,
        ]);
    }

    /**
     * Update vendor rating.
     *
     * @param int $vendorId
     * @return void
     */
    private function updateVendorRating($vendorId)
    {
        $vendor = Vendor::find($vendorId);

        if (!$vendor) {
            return;
        }

        $reviews = Review::where('vendor_id', $vendorId)
            ->where('status', 'approved')
            ->get();

        if ($reviews->isEmpty()) {
            $vendor->update([
                'rating' => 0,
                'reviews_count' => 0,
            ]);

            return;
        }

        $averageRating = $reviews->avg('rating');
        $reviewsCount = $reviews->count();

        $vendor->update([
            'rating' => round($averageRating, 1),
            'reviews_count' => $reviewsCount,
        ]);
    }
}
