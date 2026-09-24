<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Contact Requests</h1>
        <p class="mt-1 text-sm text-slate-500">People who expressed interest through the demo system.</p>
    </div>

    {{-- Filter tabs --}}
    <div class="mb-4 flex gap-2">
        @foreach(['new' => 'New', 'responded' => 'Responded', 'all' => 'All'] as $value => $label)
            <button wire:click="filterBy('{{ $value }}')" wire:loading.attr="disabled" wire:target="filterBy('{{ $value }}')"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition disabled:opacity-60
                           {{ $filter === $value ? 'bg-sky-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
                @if($value === 'new' && $newCount > 0)
                    <span class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white">{{ $newCount }}</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Name / Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 hidden sm:table-cell">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 hidden md:table-cell">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 hidden lg:table-cell">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($requests as $req)
                    <tr class="odd:bg-white even:bg-slate-50 hover:bg-sky-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-slate-900">{{ $req->name }}</p>
                            <p class="text-xs text-slate-500">{{ $req->email }}</p>
                            @if($req->phone) <p class="text-xs text-slate-400">{{ $req->phone }}</p> @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 hidden sm:table-cell">{{ $req->company ?: '—' }}</td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <p class="text-sm text-slate-600 max-w-xs truncate" title="{{ $req->message }}">{{ $req->message }}</p>
                            @if($req->notes)
                                <p class="mt-1 text-xs text-slate-400 italic">Note: {{ $req->notes }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $req->status === 'new' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $req->status === 'new' ? 'New' : 'Responded' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 hidden lg:table-cell">{{ $req->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            @if($req->status === 'new')
                                @if($markingId === $req->id)
                                    <div class="flex flex-col gap-1">
                                        <input wire:model="notes" type="text" placeholder="Response note (optional)"
                                               wire:loading.attr="disabled" wire:target="saveResponse"
                                               class="w-full rounded border border-slate-300 px-2 py-1 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                                        <div class="flex gap-1">
                                            <button wire:click="saveResponse" wire:loading.attr="disabled" wire:target="saveResponse"
                                                    class="rounded bg-sky-600 px-2 py-1 text-xs font-medium text-white hover:bg-sky-700 disabled:opacity-60">
                                                <span wire:loading.remove wire:target="saveResponse">Save</span>
                                                <span wire:loading wire:target="saveResponse">Saving…</span>
                                            </button>
                                            <button wire:click="cancelRespond" wire:loading.attr="disabled" wire:target="saveResponse"
                                                    class="rounded border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-60">Cancel</button>
                                        </div>
                                    </div>
                                @else
                                    <button wire:click="startRespond({{ $req->id }})" wire:loading.attr="disabled" wire:target="startRespond({{ $req->id }})"
                                            class="rounded border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 whitespace-nowrap disabled:opacity-60">
                                        <span wire:loading.remove wire:target="startRespond({{ $req->id }})">Mark responded</span>
                                        <span wire:loading wire:target="startRespond({{ $req->id }})">Loading…</span>
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                            No {{ $filter !== 'all' ? $filter : '' }} contact requests yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>
