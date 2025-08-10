<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MeilisearchService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

class MeilisearchImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:import 
                            {model : The model to import (e.g., App\\Models\\Product)}
                            {--chunk=100 : Number of records to process at a time}
                            {--force : Force import even if index already exists}
                            {--recreate : Recreate index before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import model data into Meilisearch';

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
        $modelClass = $this->argument('model');
        $chunkSize = (int) $this->option('chunk');
        $force = $this->option('force');
        $recreate = $this->option('recreate');

        try {
            // Check if model exists
            if (!class_exists($modelClass)) {
                $this->error("Model class {$modelClass} not found.");
                return 1;
            }

            // Check if model is searchable
            if (!in_array(\App\Traits\MeilisearchSearchable::class, class_uses($modelClass))) {
                $this->error("Model {$modelClass} does not use the MeilisearchSearchable trait.");
                return 1;
            }

            $modelInstance = new $modelClass();
            $indexName = $modelInstance->getSearchIndexName();

            $this->info("Importing {$modelClass} into index {$indexName}");

            // Check if index exists
            $index = $this->meilisearch->getIndex($indexName);
            if ($index && !$force && !$recreate) {
                $this->error("Index {$indexName} already exists. Use --force to update or --recreate to recreate.");
                return 1;
            }

            // Recreate index if requested
            if ($recreate) {
                $this->info("Recreating index {$indexName}");
                $this->meilisearch->deleteIndex($indexName);
                $this->meilisearch->createIndex($indexName);
            }

            // Get all records
            $totalRecords = $modelClass::count();
            $processed = 0;
            $imported = 0;
            $skipped = 0;

            $this->info("Found {$totalRecords} records to import.");

            // Process in chunks
            $modelClass::chunk($chunkSize, function ($records) use (&$processed, &$imported, &$skipped, $indexName) {
                $documents = [];

                foreach ($records as $record) {
                    $processed++;

                    // Convert record to searchable array
                    $document = $record->toSearchableArray();

                    // Ensure the document has an ID
                    if (!isset($document['id'])) {
                        $document['id'] = $record->id;
                    }

                    $documents[] = $document;
                }

                // Index documents
                if (!empty($documents)) {
                    $result = $this->meilisearch->indexDocuments($indexName, $documents);

                    if ($result) {
                        $imported += count($documents);
                        $this->info("Imported {$imported} of {$processed} records...");
                    } else {
                        $skipped += count($documents);
                        $this->error("Failed to import {$processed} records...");
                    }
                }
            });

            $this->info("Import completed: {$imported} records imported, {$skipped} records skipped.");
            return 0;

        } catch (Exception $e) {
            Log::error('Error importing into Meilisearch: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}