<?php

namespace App\Console\Commands;

use App\Jobs\EmbedMaintenanceRequest;
use App\Models\MaintenanceRequest;
use Illuminate\Console\Command;

/**
 * Safety net for the async embedding pipeline: dispatches embedding jobs
 * for any maintenance request that still has no embedding (e.g. the queue
 * worker was down when the request was logged).
 */
class ProcessMaintenanceEmbeddingsCommand extends Command
{
    protected $signature = 'embeddings:process-maintenance';

    protected $description = 'Dispatch embedding jobs for maintenance requests missing an embedding.';

    public function handle(): int
    {
        $ids = MaintenanceRequest::whereNull('embedding')->pluck('id');

        foreach ($ids as $id) {
            EmbedMaintenanceRequest::dispatch($id);
        }

        $this->info("Dispatched {$ids->count()} embedding job(s).");

        return self::SUCCESS;
    }
}
