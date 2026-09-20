@php
    $severityColors = [
        'low' => 'border-slate-200 bg-slate-50',
        'medium' => 'border-amber-200 bg-amber-50',
        'high' => 'border-orange-200 bg-orange-50',
        'critical' => 'border-red-300 bg-red-50',
    ];
    $dotColors = [
        'low' => 'bg-slate-400',
        'medium' => 'bg-amber-500',
        'high' => 'bg-orange-500',
        'critical' => 'bg-red-600',
    ];
    $severity = $event->signal->severity;
@endphp
<div class="flex items-start justify-between gap-3 rounded-lg border p-3 {{ $severityColors[$severity] ?? 'border-slate-200 bg-white' }}">
    <div class="flex items-start gap-3">
        <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $dotColors[$severity] ?? 'bg-slate-400' }}"></span>
        <div>
            <p class="text-sm font-medium text-slate-900">{{ $event->signal->name }}</p>
            <p class="mt-0.5 text-xs text-slate-600">
                @if($event->property)
                    {{ $event->property->fullAddress() }}
                @endif
                @if($event->tenancy)
                    · {{ $event->tenancy->tenant_name }}
                @endif
                @if($event->request)
                    · Request #{{ $event->request->id }}
                @endif
            </p>
            @if($event->note)
                <p class="mt-1 text-xs text-slate-500">{{ $event->note }}</p>
            @endif
            <p class="mt-1 text-xs text-slate-400">{{ $event->triggered_at->diffForHumans() }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('signals.resolve', $event) }}">
        @csrf
        <button class="shrink-0 rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">
            Resolve
        </button>
    </form>
</div>
