<?php

namespace App\Livewire;

use App\Models\Tenancy;
use App\Services\Lease\LeaseAssistantService;
use Illuminate\Support\Collection;
use Livewire\Component;

class LeaseAssistant extends Component
{
    public Tenancy $tenancy;

    public string $question = '';

    public ?string $answer = null;

    public Collection $sources;

    public ?string $error = null;

    public function mount(): void
    {
        $this->sources = collect();
    }

    public function ask(LeaseAssistantService $assistant): void
    {
        $this->validate(['question' => 'required|string|max:500']);

        $this->answer = null;
        $this->error = null;

        if ($this->tenancy->leaseFailed()) {
            $this->error = 'Processing this lease failed. Retry it from the Lease document panel above, then ask again.';

            return;
        }

        if (! $this->tenancy->leaseIsEmbedded()) {
            $this->error = 'This tenancy\'s lease has not finished processing yet — try again shortly.';

            return;
        }

        try {
            $result = $assistant->ask($this->tenancy, $this->question);
            $this->answer = $result['answer'];
            $this->sources = $result['sources'];
        } catch (\Throwable $e) {
            $this->error = 'The lease assistant is unavailable right now. Please try again in a moment.';
        }
    }

    public function render()
    {
        return view('livewire.lease-assistant');
    }
}
