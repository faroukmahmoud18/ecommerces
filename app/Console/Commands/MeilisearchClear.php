<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MeilisearchService;
use Illuminate\Support\Facades\Log;
use Exception;

class MeilisearchClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:clear 
                            {--all : Clear all indexes}
                            {--model= : Clear specific model index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Meilisearch indexes';

    /**
     * The Meilisearch service instance.
     *
     * @var \App\Services\MeilisearchService
     */
    protected $meilisearch;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\MeilisearchService $meilisearch
     * @return void
     */
    public function __construct(MeilisearchService $meilisearch)
    {
        parent::__construct();
        $this->meilisearch = $meilisearch;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clearAll = $this->option('all');
        $model = $this->option('model');

        try {
            if ($clearAll) {
                $this->clearAllIndexes();
            } elseif ($model) {
                $this->clearModelIndex($model);
            } else {
                $this->error('Please specify either --all or --model option.');
                return 1;
            }

            return 0;

        } catch (Exception $e) {
            Log::error('Error clearing Meilisearch: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Clear all indexes.
     *
     * @return void
     */
    protected function clearAllIndexes()
    {
        if ($this->confirm('Are you sure you want to clear all Meilisearch indexes?')) {
            $this->info('Clearing all indexes...');

            if ($this->meilisearch->clearAllIndexes()) {
                $this->info('All indexes cleared successfully.');
            } else {
                $this->error('Failed to clear all indexes.');
            }
        }
    }

    /**
     * Clear a specific model index.
     *
     * @param string $model
     * @return void
     */
    protected function clearModelIndex(string $model)
    {
        // Check if model exists
        if (!class_exists($model)) {
            $this->error("Model class {$model} not found.");
            return;
        }

        // Check if model is searchable
        if (!in_array(\App\Traits\MeilisearchSearchable::class, class_uses($model))) {
            $this->error("Model {$model} does not use the MeilisearchSearchable trait.");
            return;
        }

        $modelInstance = new $model();
        $indexName = $modelInstance->getSearchIndexName();

        if ($this->confirm("Are you sure you want to clear the {$indexName} index?")) {
            $this->info("Clearing {$indexName} index...");

            if ($this->meilisearch->deleteIndex($indexName)) {
                $this->info("Index {$indexName} cleared successfully.");
            } else {
                $this->error("Failed to clear index {$indexName}.");
            }
        }
    }
}