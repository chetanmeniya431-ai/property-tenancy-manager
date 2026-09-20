<?php

namespace App\Services\Lease;

use App\Models\LeaseChunk;
use App\Models\Tenancy;
use App\Services\Ollama\OllamaClient;
use Pgvector\Laravel\Distance;

/**
 * RAG over a single tenancy's lease. Every query is scoped with
 * `where('tenancy_id', ...)` before the vector search runs, so one
 * tenant's lease can never leak into another tenancy's answer.
 */
class LeaseAssistantService
{
    protected const TOP_K = 5;

    public function __construct(protected OllamaClient $ollama) {}

    /**
     * @return array{answer: string, sources: \Illuminate\Support\Collection<int, LeaseChunk>}
     */
    public function ask(Tenancy $tenancy, string $question): array
    {
        $chunks = $this->retrieve($tenancy, $question);

        if ($chunks->isEmpty()) {
            return [
                'answer' => 'No lease has been ingested for this tenancy yet, so there is nothing to search.',
                'sources' => $chunks,
            ];
        }

        $context = $chunks->map(fn (LeaseChunk $chunk, int $i) => '['.($i + 1).'] '.$chunk->chunk_text)->implode("\n\n");

        $system = 'You are a lease assistant for a UK-style residential/commercial tenancy. '
            .'Answer ONLY using the provided lease excerpts. If the lease excerpts do not '
            .'contain the answer, say so plainly instead of guessing. Quote the relevant '
            .'phrase from the excerpt(s) you used. Keep the answer to a few sentences.';

        $prompt = "Lease excerpts:\n{$context}\n\nQuestion: {$question}\n\nAnswer:";

        $answer = $this->ollama->generate($prompt, $system);

        return [
            'answer' => $answer,
            'sources' => $chunks,
        ];
    }

    /**
     * The one-line automatic obligation check shown when a maintenance
     * request is logged. Informational only — never a decision.
     */
    public function obligationNote(Tenancy $tenancy, string $categoryLabel): ?string
    {
        $question = "Who is responsible for {$categoryLabel} repairs under this lease — the landlord or the tenant?";

        $chunks = $this->retrieve($tenancy, $question, topK: 3);

        if ($chunks->isEmpty()) {
            return null;
        }

        $context = $chunks->map(fn (LeaseChunk $chunk) => $chunk->chunk_text)->implode("\n\n");

        $system = 'You are a lease assistant. Using ONLY the excerpts given, answer in ONE short '
            .'sentence starting with "Under this lease, " stating whether the landlord or tenant is '
            .'typically responsible for the given category of repair, and reference the likely section '
            .'if a clause/section number appears in the excerpt. This is informational only, not a decision.';

        $prompt = "Lease excerpts:\n{$context}\n\nCategory of issue: {$categoryLabel}\n\nOne-sentence note:";

        return $this->ollama->generate($prompt, $system);
    }

    /**
     * @return \Illuminate\Support\Collection<int, LeaseChunk>
     */
    protected function retrieve(Tenancy $tenancy, string $question, int $topK = self::TOP_K)
    {
        $queryVector = $this->ollama->embed($question);

        return LeaseChunk::query()
            ->where('tenancy_id', $tenancy->id)
            ->nearestNeighbors('embedding', $queryVector, Distance::Cosine)
            ->limit($topK)
            ->get();
    }
}
