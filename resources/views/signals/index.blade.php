<x-layout title="Signals">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Signals</h1>

    <section class="mb-8">
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Open ({{ $open->count() }})</h2>
        @if($open->isEmpty())
            <p class="text-sm text-slate-500">No open signals right now.</p>
        @else
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($open as $event)
                    <x-signal-card :event="$event" />
                @endforeach
            </div>
        @endif
    </section>

    <section>
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Recently resolved</h2>
        @if($resolved->isEmpty())
            <p class="text-sm text-slate-500">Nothing resolved yet.</p>
        @else
            <div class="space-y-2">
                @foreach($resolved as $event)
                    <div class="rounded-lg border border-slate-200 bg-white p-3 text-sm">
                        <p class="font-medium text-slate-700">{{ $event->signal->name }}</p>
                        <p class="text-xs text-slate-500">
                            Resolved by {{ $event->resolvedBy->name ?? '—' }} · {{ $event->resolved_at->diffForHumans() }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layout>
