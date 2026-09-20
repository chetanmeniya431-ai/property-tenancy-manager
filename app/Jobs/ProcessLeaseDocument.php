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

class ProcessLeaseDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected int $tenancyId, protected string $diskPath) {}

    public function handle(LeaseIngestionService $ingestion): void
    {
        $tenancy = Tenancy::findOrFail($this->tenancyId);
        $absolutePath = Storage::disk('leases')->path($this->diskPath);

        $ingestion->ingest($tenancy, $absolutePath);
    }
}
