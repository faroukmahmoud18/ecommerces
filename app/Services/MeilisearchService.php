<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;
use Meilisearch\Client;

class MeilisearchService
{
    protected $client;
    protected $config;
    protected $prefix;
    protected $throttle;

    /**
     * Create a new MeilisearchService instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = Config::get('meilisearch');
        $this->prefix = $this->config['prefix'];
        $this->throttle = $this->config['throttle'];

        $options = [];
        if (!empty($this->config['key'])) {
            $options['apiKey'] = $this->config['key'];
        }

        $this->client = new Client($this->config['host'], $options);
    }

    /**
     * Get Meilisearch client.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Create a new index.
     *
     * @param string $name
     * @param array $settings
     * @return \Meilisearch\Index
     */
    public function createIndex(string $name, array $settings = []): \Meilisearch\Index
    {
        $indexName = $this->prefix . $name;
        $settings = array_merge($this->config['default_settings'], $settings);

        $index = $this->client->createIndex($indexName);
        $index->updateSettings($settings);

        return $index;
    }

    /**
     * Get an index by name.
     *
     * @param string $name
     * @return \Meilisearch\Index|null
     */
    public function getIndex(string $name): ?\Meilisearch\Index
    {
        try {
            $indexName = $this->prefix . $name;
            return $this->client->getIndex($indexName);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Delete an index by name.
     *
     * @param string $name
     * @return bool
     */
    public function deleteIndex(string $name): bool
    {
        try {
            $indexName = $this->prefix . $name;
            $this->client->deleteIndex($indexName);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Search in an index.
     *
     * @param string $indexName
     * @param string $query
     * @param array $options
     * @return array
     */
    public function search(string $indexName, string $query, array $options = []): array
    {
        // Check if search is throttled
        if ($this->throttle['enabled']) {
            $key = 'meilisearch_search_' . $indexName . '_' . request()->ip();
            $attempts = Cache::get($key, 0);

            if ($attempts >= $this->throttle['max_attempts']) {
                return [
                    'hits' => [],
                    'hitsPerPage' => 0,
                    'totalHits' => 0,
                    'query' => $query,
                    'error' => 'Search rate limit exceeded. Please try again later.'
                ];
            }

            Cache::put($key, $attempts + 1, now()->addMinutes($this->throttle['decay_minutes']));
        }

        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return [
                    'hits' => [],
                    'hitsPerPage' => 0,
                    'totalHits' => 0,
                    'query' => $query,
                    'error' => 'Index not found'
                ];
            }

            // Merge with default search settings
            $searchSettings = array_merge($this->config['search_settings'], $options);

            // Perform search
            $result = $index->search($query, $searchSettings);

            // Add query to result for debugging
            $result['query'] = $query;

            return $result;
        } catch (Exception $e) {
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
     * Index a document.
     *
     * @param string $indexName
     * @param array $document
     * @param string $primaryKey
     * @return bool
     */
    public function indexDocument(string $indexName, array $document, string $primaryKey = 'id'): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                $index = $this->createIndex($indexName);
            }

            $index->addDocuments([$document], $primaryKey);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Index multiple documents.
     *
     * @param string $indexName
     * @param array $documents
     * @param string $primaryKey
     * @return bool
     */
    public function indexDocuments(string $indexName, array $documents, string $primaryKey = 'id'): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                $index = $this->createIndex($indexName);
            }

            $index->addDocuments($documents, $primaryKey);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update a document.
     *
     * @param string $indexName
     * @param array $document
     * @param string $primaryKey
     * @return bool
     */
    public function updateDocument(string $indexName, array $document, string $primaryKey = 'id'): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return false;
            }

            $index->updateDocuments([$document], $primaryKey);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete a document.
     *
     * @param string $indexName
     * @param string $documentId
     * @return bool
     */
    public function deleteDocument(string $indexName, string $documentId): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return false;
            }

            $index->deleteDocument($documentId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete multiple documents.
     *
     * @param string $indexName
     * @param array $documentIds
     * @return bool
     */
    public function deleteDocuments(string $indexName, array $documentIds): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return false;
            }

            $index->deleteDocuments($documentIds);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all indexes.
     *
     * @return array
     */
    public function getIndexes(): array
    {
        try {
            $indexes = $this->client->getRawIndexes();
            return array_map(function($index) {
                return str_replace($this->prefix, '', $index['uid']);
            }, $indexes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Clear all indexes.
     *
     * @return bool
     */
    public function clearAllIndexes(): bool
    {
        try {
            $indexes = $this->getIndexes();

            foreach ($indexes as $indexName) {
                $this->deleteIndex($indexName);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Configure synonyms for an index.
     *
     * @param string $indexName
     * @param array $synonyms
     * @return bool
     */
    public function configureSynonyms(string $indexName, array $synonyms): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return false;
            }

            $index->updateSettings(['synonyms' => $synonyms]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Configure stop words for an index.
     *
     * @param string $indexName
     * @param array $stopWords
     * @return bool
     */
    public function configureStopWords(string $indexName, array $stopWords): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return false;
            }

            $index->updateSettings(['stopWords' => $stopWords]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Configure ranking rules for an index.
     *
     * @param string $indexName
     * @param array $rankingRules
     * @return bool
     */
    public function configureRankingRules(string $indexName, array $rankingRules): bool
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return false;
            }

            $index->updateSettings(['rankingRules' => $rankingRules]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get index settings.
     *
     * @param string $indexName
     * @return array
     */
    public function getIndexSettings(string $indexName): array
    {
        try {
            $index = $this->getIndex($indexName);

            if (!$index) {
                return [];
            }

            return $index->getRawSettings();
        } catch (Exception $e) {
            return [];
        }
    }
}