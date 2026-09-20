<?php

namespace App\Console\Commands;

use App\Jobs\ProcessLeaseDocument;
use App\Models\Tenancy;
use Illuminate\Console\Command;

/**
 * Daily reconciliation: any tenancy with a lease file on disk but no
 * lease_embedded_at (upload succeeded, embedding job never completed —
 * e.g. queue worker was down) gets re-queued.
 */
class CheckLeaseEmbeddingsCommand extends Command
{
    protected $signature = 'leases:check-embeddings';

    protected $description = 'Re-queue lease ingestion for any tenancy whose lease was uploaded but never embedded.';

    public function handle(): int
    {
        $tenancies = Tenancy::whereNotNull('lease_file_path')
            ->whereNull('lease_embedded_at')
            ->get();

        foreach ($tenancies as $tenancy) {
            ProcessLeaseDocument::dispatch($tenancy->id, $tenancy->lease_file_path);
        }

        $this->info("Re-queued {$tenancies->count()} lease import(s).");

        return self::SUCCESS;
    }
}
