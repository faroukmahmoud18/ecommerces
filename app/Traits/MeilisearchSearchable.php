<?php

namespace App\Traits;

use App\Services\MeilisearchService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

trait MeilisearchSearchable
{
    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function getSearchIndexName(): string
    {
        $config = Config::get('meilisearch.indexable_models');
        $className = get_class($this);

        if (isset($config[$className]) && isset($config[$className]['index'])) {
            return $config[$className]['index'];
        }

        return class_basename($this);
    }

    /**
     * Get the searchable attributes for the model.
     *
     * @return array
     */
    public function getSearchableAttributes(): array
    {
        $config = Config::get('meilisearch.indexable_models');
        $className = get_class($this);

        if (isset($config[$className]) && isset($config[$className]['searchable'])) {
            return $config[$className]['searchable'];
        }

        return $this->searchable ?? [];
    }

    /**
     * Get the filterable attributes for the model.
     *
     * @return array
     */
    public function getFilterableAttributes(): array
    {
        $config = Config::get('meilisearch.indexable_models');
        $className = get_class($this);

        if (isset($config[$className]) && isset($config[$className]['filterable'])) {
            return $config[$className]['filterable'];
        }

        return $this->filterable ?? [];
    }

    /**
     * Get the sortable attributes for the model.
     *
     * @return array
     */
    public function getSortableAttributes(): array
    {
        $config = Config::get('meilisearch.indexable_models');
        $className = get_class($this);

        if (isset($config[$className]) && isset($config[$className]['sortable'])) {
            return $config[$className]['sortable'];
        }

        return $this->sortable ?? [];
    }

    /**
     * Get the displayed attributes for the model.
     *
     * @return array
     */
    public function getDisplayedAttributes(): array
    {
        $config = Config::get('meilisearch.indexable_models');
        $className = get_class($this);

        if (isset($config[$className]) && isset($config[$className]['displayed'])) {
            return $config[$className]['displayed'];
        }

        return $this->displayed ?? [];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return $this->toArray();
    }

    /**
     * Get the Meilisearch service instance.
     *
     * @return MeilisearchService
     */
    public function getSearchService(): MeilisearchService
    {
        return app(MeilisearchService::class);
    }

    /**
     * Update the model's index in Meilisearch.
     *
     * @return bool
     */
    public function searchable(): bool
    {
        try {
            $indexName = $this->getSearchIndexName();
            $document = $this->toSearchableArray();

            // Ensure the document has an ID
            if (!isset($document['id'])) {
                $document['id'] = $this->id;
            }

            return $this->getSearchService()->indexDocument($indexName, $document, 'id');
        } catch (Exception $e) {
            Log::error('Error updating searchable index: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove the model from the Meilisearch index.
     *
     * @return bool
     */
    public function unsearchable(): bool
    {
        try {
            $indexName = $this->getSearchIndexName();
            $documentId = $this->id;

            return $this->getSearchService()->deleteDocument($indexName, (string) $documentId);
        } catch (Exception $e) {
            Log::error('Error removing from searchable index: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Make the model searchable in Meilisearch.
     *
     * @return void
     */
    public function makeSearchable()
    {
        $this->searchable();
    }

    /**
     * Remove the model from the Meilisearch index.
     *
     * @return void
     */
    public function removeFromSearch()
    {
        $this->unsearchable();
    }

    /**
     * Boot the searchable trait for the model.
     *
     * @return void
     */
    protected static function bootMeilisearchSearchable()
    {
        static::created(function ($model) {
            $model->searchable();
        });

        static::updated(function ($model) {
            $model->searchable();
        });

        static::deleted(function ($model) {
            $model->unsearchable();
        });
    }

    /**
     * Perform a search on the model's index.
     *
     * @param string $query
     * @param array $options
     * @return array
     */
    public static function search(string $query, array $options = []): array
    {
        try {
            $instance = new static;
            $indexName = $instance->getSearchIndexName();
            $service = $instance->getSearchService();

            // Merge default options
            $defaultOptions = [
                'filterableAttributes' => $instance->getFilterableAttributes(),
                'sortableAttributes' => $instance->getSortableAttributes(),
                'displayedAttributes' => $instance->getDisplayedAttributes(),
            ];

            $options = array_merge($defaultOptions, $options);

            return $service->search($indexName, $query, $options);
        } catch (Exception $e) {
            Log::error('Error performing search: ' . $e->getMessage());
            return [
                'hits' => [],
                'hitsPerPage' => 0,
                'totalHits' => 0,
                'query' => $query,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Import all models into Meilisearch.
     *
     * @return bool
     */
    public static function importIntoSearch(): bool
    {
        try {
            $instance = new static;
            $indexName = $instance->getSearchIndexName();
            $service = $instance->getSearchService();

            // Get all models
            $models = static::all();

            // Prepare documents
            $documents = $models->map(function ($model) {
                $document = $model->toSearchableArray();

                // Ensure the document has an ID
                if (!isset($document['id'])) {
                    $document['id'] = $model->id;
                }

                return $document;
            })->toArray();

            // Index all documents
            return $service->indexDocuments($indexName, $documents, 'id');
        } catch (Exception $e) {
            Log::error('Error importing into searchable index: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove all models from Meilisearch.
     *
     * @return bool
     */
    public static function removeFromSearch(): bool
    {
        try {
            $instance = new static;
            $indexName = $instance->getSearchIndexName();
            $service = $instance->getSearchService();

            // Get all models
            $models = static::all();

            // Prepare document IDs
            $documentIds = $models->pluck('id')->toArray();

            // Delete all documents
            return $service->deleteDocuments($indexName, $documentIds);
        } catch (Exception $e) {
            Log::error('Error removing from searchable index: ' . $e->getMessage());
            return false;
        }
    }
}