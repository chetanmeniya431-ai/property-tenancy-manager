<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-700">Lease document</h3>

    @if($tenancy->hasLease())
        {{-- Current file on record --}}
        <div class="mt-2 flex items-start justify-between gap-3 rounded-md bg-slate-50 px-3 py-2">
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-slate-800">{{ $tenancy->lease_original_filename ?? 'Lease document' }}</p>
                <p class="mt-0.5 text-xs text-slate-500">
                    @if($tenancy->lease_uploaded_at)
                        Uploaded {{ $tenancy->lease_uploaded_at->diffForHumans() }} &middot;
                    @endif
                    @if($tenancy->leaseIsEmbedded())
                        <span class="font-medium text-emerald-600">Indexed and searchable</span>
                    @elseif($tenancy->leaseFailed())
                        <span class="font-medium text-red-600">Processing failed</span>
                    @else
                        <span class="font-medium text-amber-600">Processing…</span>
                    @endif
                </p>
            </div>
            @if($tenancy->leaseFailed())
                <button wire:click="retry" wire:loading.attr="disabled" wire:target="retry"
                        class="shrink-0 rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-50">
                    <span wire:loading.remove wire:target="retry">Retry</span>
                    <span wire:loading wire:target="retry">Retrying…</span>
                </button>
            @endif
        </div>
        @if($tenancy->leaseFailed())
            <p class="mt-1 text-xs text-red-600">{{ $tenancy->lease_processing_error }}</p>
        @endif
    @else
        <p class="mt-1 text-xs text-slate-500">No lease uploaded yet.</p>
    @endif

    @if($justUploaded)
        <p class="mt-2 rounded-md bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            Lease uploaded — it's being chunked and embedded now via the queue.
        </p>
    @endif

    <p class="mt-4 text-xs font-medium text-slate-600">{{ $tenancy->hasLease() ? 'Replace the lease document' : 'Upload the lease document' }}</p>
    <form wire:submit="save" class="mt-1.5 flex items-center gap-3">
        <input type="file" wire:model="lease" accept="application/pdf" wire:key="lease-input-{{ $uploadKey }}"
               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-sky-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-sky-700 hover:file:bg-sky-100">
        <button type="submit" wire:loading.attr="disabled" wire:target="save,lease"
                class="shrink-0 rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500 disabled:opacity-50">
            <span wire:loading.remove wire:target="save,lease">Upload</span>
            <span wire:loading wire:target="save,lease">Uploading…</span>
        </button>
    </form>
    @error('lease') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
