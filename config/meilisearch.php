<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meilisearch Host
    |--------------------------------------------------------------------------
    |
    | The host address of your Meilisearch instance.
    |
    */

    'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),

    /*
    |--------------------------------------------------------------------------
    | Meilisearch API Key
    |--------------------------------------------------------------------------
    |
    | The API key for your Meilisearch instance. If no key is provided, requests
    | will be sent without authentication.
    |
    */

    'key' => env('MEILISEARCH_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Index Settings
    |--------------------------------------------------------------------------
    |
    | Default settings to apply when creating new indexes.
    |
    */

    'default_settings' => [
        'searchableAttributes' => [
            'name',
            'description',
            'category',
            'brand',
            'tags',
            'sku',
            'vendor_name',
        ],
        'filterableAttributes' => [
            'category',
            'brand',
            'price',
            'in_stock',
            'has_discount',
            'vendor_id',
        ],
        'sortableAttributes' => [
            'name',
            'price',
            'created_at',
            'popularity',
        ],
        'displayedAttributes' => [
            'id',
            'name',
            'description',
            'price',
            'original_price',
            'image',
            'thumbnail',
            'rating',
            'reviews_count',
            'in_stock',
            'has_discount',
            'discount_percentage',
            'category',
            'brand',
            'vendor_id',
            'vendor_name',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Search Settings
    |--------------------------------------------------------------------------
    |
    | Default search settings to apply when performing searches.
    |
    */

    'search_settings' => [
        'limit' => 20,
        'offset' => 0,
        'attributesToRetrieve' => [
            'id',
            'name',
            'description',
            'price',
            'image',
            'rating',
            'category',
            'brand',
            'vendor_name',
            'highlight',
        ],
        'attributesToCrop' => [
            'description' => [
                'length' => 20,
                'separator' => '...',
            ],
        ],
        'cropMarker' => '[...]',
        'matchingStrategy' => 'all',
    ],

    /*
    |--------------------------------------------------------------------------
    | Indexable Models
    |--------------------------------------------------------------------------
    |
    | Configure which models should be indexed in Meilisearch.
    |
    */

    'indexable_models' => [
        \App\Models\Product::class => [
            'index' => 'products',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ],
        \App\Models\Category::class => [
            'index' => 'categories',
            'searchable' => true,
            'filterable' => true,
            'sortable' => false,
        ],
        \App\Models\Vendor::class => [
            'index' => 'vendors',
            'searchable' => true,
            'filterable' => true,
            'sortable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | A prefix to add to all indexes to avoid conflicts.
    |
    */

    'prefix' => env('MEILISEARCH_PREFIX', 'ecommerce_'),

    /*
    |--------------------------------------------------------------------------
    | Throttle Settings
    |
    | Configure the throttling settings for search requests.
    |
    */

    'throttle' => [
        'enabled' => env('MEILISEARCH_THROTTLE_ENABLED', true),
        'max_attempts' => env('MEILISEARCH_THROTTLE_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('MEILISEARCH_THROTTLE_DECAY_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Indexable Attributes
    |--------------------------------------------------------------------------
    |
    | Default attributes to include when indexing models.
    |
    */

    'default_attributes' => [
        'products' => [
            'id',
            'name',
            'description',
            'price',
            'original_price',
            'image',
            'thumbnail',
            'rating',
            'reviews_count',
            'in_stock',
            'has_discount',
            'discount_percentage',
            'category',
            'brand',
            'tags',
            'sku',
            'vendor_id',
            'vendor_name',
            'created_at',
            'updated_at',
            'popularity',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Synonyms
    |--------------------------------------------------------------------------
    |
    | Configure synonyms for search terms.
    |
    */

    'synonyms' => [
        'products' => [
            ['iphone', 'apple iphone'],
            ['samsung galaxy', 'samsung'],
            ['mobile', 'cell phone'],
            ['laptop', 'notebook'],
            ['tablet', 'tab'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stop Words
    |--------------------------------------------------------------------------
    |
    | Configure stop words to ignore during search.
    |
    */

    'stop_words' => [
        'products' => [
            'a',
            'an',
            'the',
            'and',
            'or',
            'but',
            'in',
            'on',
            'at',
            'to',
            'for',
            'of',
            'with',
            'by',
            'is',
            'are',
            'was',
            'were',
        ],
    ],
];