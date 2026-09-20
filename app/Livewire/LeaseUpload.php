<?php

namespace App\Livewire;

use App\Jobs\ProcessLeaseDocument;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaseUpload extends Component
{
    use WithFileUploads;

    public Tenancy $tenancy;

    #[Validate('required|file|mimes:pdf|max:20480')]
    public $lease = null;

    public bool $justUploaded = false;

    public function save(): void
    {
        $this->validate();

        $storedName = 'tenancy-'.$this->tenancy->id.'-'.Str::random(8).'.pdf';
        Storage::disk('leases')->put($storedName, file_get_contents($this->lease->getRealPath()));

        $this->tenancy->forceFill([
            'lease_file_path' => $storedName,
            'lease_embedded_at' => null,
        ])->save();

        ProcessLeaseDocument::dispatch($this->tenancy->id, $storedName);

        $this->lease = null;
        $this->justUploaded = true;
    }

    public function render()
    {
        $this->tenancy->refresh();

        return view('livewire.lease-upload');
    }
}
