<?php

namespace App\Services\Lease;

use App\Models\LeaseChunk;
use App\Models\Tenancy;
use App\Services\Ollama\OllamaClient;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * The single entry point for turning a lease PDF into searchable
 * `lease_chunks`. Used identically by:
 *  - the Livewire upload component (UI path),
 *  - php artisan leases:import (manual/CLI path),
 *  - the database seeder (synthetic demo data).
 *
 * Re-running this for a tenancy always replaces its existing chunks.
 */
class LeaseIngestionService
{
    protected const WORDS_PER_CHUNK = 500;

    public function __construct(
        protected PdfParser $parser,
        protected OllamaClient $ollama,
    ) {}

    /**
     * @param  string  $absolutePath  Path to a PDF file on disk.
     */
    public function ingest(Tenancy $tenancy, string $absolutePath): void
    {
        $text = $this->extractText($absolutePath);
        $chunks = $this->chunk($text);

        DB::transaction(function () use ($tenancy, $chunks) {
            LeaseChunk::where('tenancy_id', $tenancy->id)->delete();

            foreach ($chunks as $index => $chunkText) {
                LeaseChunk::create([
                    'tenancy_id' => $tenancy->id,
                    'chunk_index' => $index,
                    'chunk_text' => $chunkText,
                    'embedding' => $this->ollama->embed($chunkText),
                    'created_at' => now(),
                ]);
            }

            $tenancy->forceFill(['lease_embedded_at' => now()])->save();
        });
    }

    protected function extractText(string $absolutePath): string
    {
        $pdf = $this->parser->parseFile($absolutePath);
        $text = $pdf->getText();

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * @return string[]
     */
    protected function chunk(string $text): array
    {
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return [];
        }

        return array_map(
            fn (array $group) => implode(' ', $group),
            array_chunk($words, self::WORDS_PER_CHUNK)
        );
    }
}
