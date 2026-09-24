<?php

namespace App\Livewire;

use App\Jobs\ProcessLeaseDocument;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaseUpload extends Component
{
    use WithFileUploads;

    public Tenancy $tenancy;

    // No #[Validate] attribute here on purpose: Livewire re-validates the
    // property on every update while the file is mid-upload, which briefly
    // shows a false "required" error before the upload finishes. Validating
    // only inside save() (once, on submit) avoids that.
    public $lease = null;

    public bool $justUploaded = false;

    // Bumped after every upload/retry so the file input below gets a new
    // wire:key — Livewire then replaces the DOM node instead of reusing it,
    // which is what actually clears the browser's "selected file" display.
    public int $uploadKey = 0;

    public function save(): void
    {
        $this->validate(['lease' => 'required|file|mimes:pdf|max:20480']);

        $storedName = 'tenancy-'.$this->tenancy->id.'-'.Str::random(8).'.pdf';
        Storage::disk('leases')->put($storedName, file_get_contents($this->lease->getRealPath()));

        $this->tenancy->forceFill([
            'lease_file_path' => $storedName,
            'lease_original_filename' => $this->lease->getClientOriginalName(),
            'lease_uploaded_at' => now(),
            'lease_embedded_at' => null,
            'lease_processing_error' => null,
        ])->save();

        ProcessLeaseDocument::dispatch($this->tenancy->id, $storedName);

        $this->lease = null;
        $this->uploadKey++;
        $this->justUploaded = true;
    }

    /**
     * Re-run processing on the file already on disk — no re-upload needed.
     * The stored PDF is untouched by a failed embedding attempt; only the
     * embedding step needs to run again.
     */
    public function retry(): void
    {
        $this->tenancy->forceFill([
            'lease_embedded_at' => null,
            'lease_processing_error' => null,
        ])->save();

        ProcessLeaseDocument::dispatch($this->tenancy->id, $this->tenancy->lease_file_path);

        $this->justUploaded = true;
    }

    public function render()
    {
        $this->tenancy->refresh();

        return view('livewire.lease-upload');
    }
}
