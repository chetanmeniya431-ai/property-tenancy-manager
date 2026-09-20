<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-700">Lease Assistant</h3>
    <p class="mt-1 text-xs text-slate-500">Ask a question about this tenancy's lease. Only this tenant's lease is searched.</p>

    <div class="mt-3 flex flex-wrap gap-1.5">
        @foreach([
            'Is the landlord responsible for fixing the boiler under this lease?',
            'What notice period does the tenant need to give to end the tenancy?',
            'Does this lease allow pets?',
        ] as $example)
            <button type="button" wire:click="$set('question', '{{ $example }}')"
                    class="rounded-full border border-slate-200 px-2.5 py-1 text-xs text-slate-500 hover:border-sky-300 hover:text-sky-700">
                {{ $example }}
            </button>
        @endforeach
    </div>

    <form wire:submit="ask" class="mt-3 flex gap-2">
        <input type="text" wire:model="question" placeholder="Ask about this lease..."
               class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
        <button type="submit" wire:loading.attr="disabled" wire:target="ask"
                class="shrink-0 rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500 disabled:opacity-50">
            <span wire:loading.remove wire:target="ask">Ask</span>
            <span wire:loading wire:target="ask">Thinking…</span>
        </button>
    </form>
    @error('question') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

    @if($error)
        <p class="mt-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ $error }}</p>
    @endif

    @if($answer)
        <div class="mt-3 rounded-md bg-sky-50 px-3 py-3 text-sm text-slate-700">
            <p>{{ $answer }}</p>
        </div>

        @if($sources->isNotEmpty())
            <details class="mt-2 text-xs text-slate-500">
                <summary class="cursor-pointer font-medium">Lease excerpts used</summary>
                <div class="mt-2 space-y-2">
                    @foreach($sources as $source)
                        <blockquote class="rounded-md border border-slate-200 bg-slate-50 p-2">{{ $source->chunk_text }}</blockquote>
                    @endforeach
                </div>
            </details>
        @endif
    @endif
</div>
