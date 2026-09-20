<x-layout title="Dashboard">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
    </div>

    @if($signalEvents->isNotEmpty())
        <section class="mb-8">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Open signals ({{ $signalEvents->count() }})</h2>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($signalEvents as $event)
                    <x-signal-card :event="$event" />
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Properties ({{ $properties->count() }})</h2>
            <a href="{{ route('properties.create') }}" class="text-sm font-medium text-sky-700 hover:text-sky-800">+ Add property</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($properties as $property)
                <a href="{{ route('properties.show', $property) }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-sky-300">
                    <p class="text-sm font-semibold text-slate-900">{{ $property->fullAddress() }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ \App\Models\Property::TYPES[$property->property_type] }}</p>

                    <div class="mt-3 flex items-center justify-between text-xs">
                        @if($property->active_tenancy)
                            <x-status-badge :status="$property->active_tenancy->status" />
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-500">Vacant</span>
                        @endif

                        @if($property->open_maintenance_count > 0)
                            <span class="font-medium text-orange-600">{{ $property->open_maintenance_count }} open request(s)</span>
                        @else
                            <span class="text-slate-400">No open requests</span>
                        @endif
                    </div>

                    @if($property->active_tenancy)
                        <p class="mt-2 text-xs text-slate-500">Next rent due: {{ $property->active_tenancy->nextRentDueDate()->format('d M Y') }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
</x-layout>
