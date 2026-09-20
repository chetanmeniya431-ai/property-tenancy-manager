<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-700">Lease document</h3>

    @if($tenancy->hasLease())
        <p class="mt-1 text-xs text-slate-500">
            A lease PDF is on file.
            @if($tenancy->leaseIsEmbedded())
                <span class="font-medium text-emerald-600">Indexed and searchable.</span>
            @else
                <span class="font-medium text-amber-600">Processing — chunking and embedding in the background.</span>
            @endif
        </p>
    @else
        <p class="mt-1 text-xs text-slate-500">No lease uploaded yet.</p>
    @endif

    @if($justUploaded)
        <p class="mt-2 rounded-md bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            Lease uploaded — it's being chunked and embedded now via the queue.
        </p>
    @endif

    <form wire:submit="save" class="mt-3 flex items-center gap-3">
        <input type="file" wire:model="lease" accept="application/pdf"
               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-sky-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-sky-700 hover:file:bg-sky-100">
        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="shrink-0 rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500 disabled:opacity-50">
            <span wire:loading.remove wire:target="save">Upload</span>
            <span wire:loading wire:target="save">Uploading…</span>
        </button>
    </form>
    @error('lease') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
