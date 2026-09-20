<?php

namespace App\Console\Commands;

use App\Models\Tenancy;
use App\Services\Lease\LeaseIngestionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Manual/CLI lease import — deliberately hits the exact same
 * LeaseIngestionService as the Livewire upload component and the seeder,
 * so a lease imported this way is indistinguishable from one uploaded
 * through the UI once ingested.
 *
 * Usage: php artisan leases:import {tenancy_id} {path}
 */
class ImportLeaseCommand extends Command
{
    protected $signature = 'leases:import {tenancy_id : The tenancy this lease belongs to} {path : Absolute path to the lease PDF on disk}';

    protected $description = 'Manually import (or re-import) a lease PDF for a tenancy, bypassing the UI uploader.';

    public function handle(LeaseIngestionService $ingestion): int
    {
        $tenancy = Tenancy::find($this->argument('tenancy_id'));

        if (! $tenancy) {
            $this->error("No tenancy found with id {$this->argument('tenancy_id')}.");

            return self::FAILURE;
        }

        $sourcePath = $this->argument('path');

        if (! is_file($sourcePath)) {
            $this->error("File not found: {$sourcePath}");

            return self::FAILURE;
        }

        $storedName = 'tenancy-'.$tenancy->id.'-'.Str::random(8).'.pdf';
        $contents = file_get_contents($sourcePath);
        Storage::disk('leases')->put($storedName, $contents);

        $tenancy->forceFill(['lease_file_path' => $storedName])->save();

        $this->info("Ingesting lease for tenancy #{$tenancy->id} ({$tenancy->tenant_name})...");

        $ingestion->ingest($tenancy, Storage::disk('leases')->path($storedName));

        $chunkCount = $tenancy->leaseChunks()->count();
        $this->info("Done. Stored {$chunkCount} lease chunk(s), embedded_at={$tenancy->refresh()->lease_embedded_at}.");

        return self::SUCCESS;
    }
}
