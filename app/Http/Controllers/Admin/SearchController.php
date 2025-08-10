<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MeilisearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SearchController extends Controller
{
    /**
     * The Meilisearch service instance.
     *
     * @var \App\Services\MeilisearchService
     */
    protected $meilisearch;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\MeilisearchService $meilisearch
     * @return void
     */
    public function __construct(MeilisearchService $meilisearch)
    {
        $this->meilisearch = $meilisearch;
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display the search analytics dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.search.index');
    }

    /**
     * Display search analytics.
     *
     * @return \Illuminate\Http\Response
     */
    public function analytics()
    {
        try {
            // Get search analytics from cache
            $analytics = Cache::remember('search_analytics', now()->addHours(1), function () {
                // In a real implementation, this would query your analytics database
                return [
                    'total_searches' => 12543,
                    'unique_searches' => 8765,
                    'average_results_per_search' => 12.4,
                    'no_results_percentage' => 8.2,
                    'popular_searches' => [
                        ['term' => 'iphone 13', 'count' => 342],
                        ['term' => 'سماعات لاسلكية', 'count' => 298],
                        ['term' => 'لابتوب', 'count' => 256],
                        ['term' => 'هاتف محمول', 'count' => 198],
                        ['term' => 'ساعة ذكية', 'count' => 176],
                    ],
                    'popular_filters' => [
                        ['filter' => 'price-0-500', 'count' => 1234],
                        ['filter' => 'category-electronics', 'count' => 987],
                        ['filter' => 'brand-apple', 'count' => 765],
                        ['filter' => 'in-stock', 'count' => 654],
                        ['filter' => 'discount', 'count' => 543],
                    ],
                    'conversion_by_search' => [
                        ['term' => 'iphone 13', 'conversions' => 42, 'conversion_rate' => 12.3],
                        ['term' => 'سماعات لاسلكية', 'conversions' => 38, 'conversion_rate' => 12.7],
                        ['term' => 'لابتوب', 'conversions' => 35, 'conversion_rate' => 13.7],
                        ['term' => 'هاتف محمول', 'conversions' => 28, 'conversion_rate' => 14.1],
                        ['term' => 'ساعة ذكية', 'conversions' => 24, 'conversion_rate' => 13.6],
                    ],
                    'search_by_hour' => [
                        ['hour' => '00:00', 'count' => 120],
                        ['hour' => '04:00', 'count' => 80],
                        ['hour' => '08:00', 'count' => 450],
                        ['hour' => '12:00', 'count' => 1200],
                        ['hour' => '16:00', 'count' => 1450],
                        ['hour' => '20:00', 'count' => 980],
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'analytics' => $analytics,
            ]);
        } catch (Exception $e) {
            Log::error('Error getting search analytics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات التحليلات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all indexes.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexes()
    {
        try {
            $indexes = $this->meilisearch->getIndexes();

            // Get detailed information for each index
            $indexDetails = [];

            foreach ($indexes as $indexName) {
                $index = $this->meilisearch->getIndex($indexName);
                $settings = $this->meilisearch->getIndexSettings($indexName);

                if ($index && $settings) {
                    $indexDetails[] = [
                        'name' => $indexName,
                        'uid' => $index->getUid(),
                        'createdAt' => $index->getCreatedAt(),
                        'updatedAt' => $index->getUpdatedAt(),
                        'primaryKey' => $index->getPrimaryKey(),
                        'numberOfDocuments' => $index->getStats()['numberOfDocuments'],
                        'fileSize' => $index->getStats()['fileSize'],
                        'lastUpdate' => $index->getStats()['lastUpdate'],
                        'settings' => $settings,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'indexes' => $indexDetails,
            ]);
        } catch (Exception $e) {
            Log::error('Error getting indexes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الفهارس',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get index settings.
     *
     * @param string $index
     * @return \Illuminate\Http\Response
     */
    public function indexSettings($index)
    {
        try {
            $settings = $this->meilisearch->getIndexSettings($index);

            return response()->json([
                'success' => true,
                'index' => $index,
                'settings' => $settings,
            ]);
        } catch (Exception $e) {
            Log::error('Error getting index settings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب إعدادات الفهرس',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update index settings.
     *
     * @param Request $request
     * @param string $index
     * @return \Illuminate\Http\Response
     */
    public function updateIndexSettings(Request $request, $index)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            $settings = $request->input('settings');
            $indexInstance = $this->meilisearch->getIndex($index);

            if (!$indexInstance) {
                return response()->json([
                    'success' => false,
                    'message' => 'الفهرس غير موجود',
                ], 404);
            }

            // Update settings
            $indexInstance->updateSettings($settings);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث إعدادات الفهرس بنجاح',
            ]);
        } catch (Exception $e) {
            Log::error('Error updating index settings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث إعدادات الفهرس',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reindex an index.
     *
     * @param string $index
     * @return \Illuminate\Http\Response
     */
    public function reindex($index)
    {
        try {
            // Get the model class for this index
            $config = Config::get('meilisearch.indexable_models');
            $modelClass = null;

            foreach ($config as $class => $options) {
                if ($options['index'] === $index) {
                    $modelClass = $class;
                    break;
                }
            }

            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على نموذج مرتبط بهذا الفهرس',
                ], 404);
            }

            // Reimport the model data
            $result = $modelClass::importIntoSearch();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إعادة فهرسة البيانات بنجاح',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في إعادة فهرسة البيانات',
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Error reindexing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة الفهرسة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an index.
     *
     * @param string $index
     * @return \Illuminate\Http\Response
     */
    public function deleteIndex($index)
    {
        try {
            $result = $this->meilisearch->deleteIndex($index);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الفهرس بنجاح',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في حذف الفهرس',
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Error deleting index: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الفهرس',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}