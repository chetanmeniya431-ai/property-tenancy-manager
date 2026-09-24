<?php

namespace App\Jobs;

use App\Models\Tenancy;
use App\Services\Lease\LeaseIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessLeaseDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected int $tenancyId, protected string $diskPath) {}

    public function handle(LeaseIngestionService $ingestion): void
    {
        $tenancy = Tenancy::findOrFail($this->tenancyId);
        $absolutePath = Storage::disk('leases')->path($this->diskPath);

        // Clear any error from a previous failed attempt so a retry doesn't
        // leave stale text showing while this attempt is in flight.
        if ($tenancy->lease_processing_error) {
            $tenancy->forceFill(['lease_processing_error' => null])->save();
        }

        $ingestion->ingest($tenancy, $absolutePath);
    }

    /**
     * Called once by the queue after all retries are exhausted. Without this,
     * a permanently-failing job (e.g. Ollama unreachable) leaves the tenancy
     * silently stuck showing "Processing" forever — the queue worker gives up
     * quietly and nothing in the UI or logs points at why.
     */
    public function failed(Throwable $e): void
    {
        Tenancy::whereKey($this->tenancyId)->update([
            'lease_processing_error' => $e->getMessage(),
        ]);
    }
}
