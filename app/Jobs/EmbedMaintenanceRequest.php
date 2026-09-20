<?php

namespace App\Jobs;

use App\Models\MaintenanceRequest;
use App\Services\Ollama\OllamaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Embeds a maintenance request's category + description with
 * nomic-embed-text, asynchronously, so logging a request never blocks on
 * an Ollama round trip. Feeds the recurring-issue pattern detection.
 */
class EmbedMaintenanceRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected int $requestId) {}

    public function handle(OllamaClient $ollama): void
    {
        $request = MaintenanceRequest::find($this->requestId);

        if (! $request || $request->embedding !== null) {
            return;
        }

        $label = MaintenanceRequest::CATEGORIES[$request->category] ?? $request->category;
        $text = "{$label}: {$request->description}";

        $request->forceFill(['embedding' => $ollama->embed($text)])->save();
    }
}
