<?php

namespace App\Services\Ollama;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around the local Ollama HTTP API. No external API key is
 * ever used — OLLAMA_BASE_URL always points at the host's own Ollama
 * instance (host.docker.internal from inside the app container).
 */
class OllamaClient
{
    protected string $baseUrl;

    protected string $generationModel;

    protected string $embeddingModel;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ollama.base_url'), '/');
        $this->generationModel = config('services.ollama.generation_model');
        $this->embeddingModel = config('services.ollama.embedding_model');
        $this->timeout = (int) config('services.ollama.timeout');
    }

    /**
     * Ask llama3.2:3b a question, optionally with a system prompt.
     */
    public function generate(string $prompt, ?string $system = null): string
    {
        $payload = [
            'model' => $this->generationModel,
            'prompt' => $prompt,
            'stream' => false,
        ];

        if ($system !== null) {
            $payload['system'] = $system;
        }

        $response = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/api/generate", $payload);

        if ($response->failed()) {
            throw new RuntimeException("Ollama generate request failed: {$response->status()} {$response->body()}");
        }

        return trim((string) $response->json('response'));
    }

    /**
     * Embed a piece of text with nomic-embed-text. Returns a 768-dim vector.
     *
     * @return float[]
     */
    public function embed(string $text): array
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/api/embeddings", [
                'model' => $this->embeddingModel,
                'prompt' => $text,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Ollama embeddings request failed: {$response->status()} {$response->body()}");
        }

        $embedding = $response->json('embedding');

        if (! is_array($embedding)) {
            throw new RuntimeException('Ollama embeddings response did not contain an embedding array.');
        }

        return $embedding;
    }
}
