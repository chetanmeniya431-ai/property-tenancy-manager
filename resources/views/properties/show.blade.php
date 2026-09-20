<x-layout title="Property">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">{{ $property->fullAddress() }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ \App\Models\Property::TYPES[$property->property_type] }} · Owner: {{ $property->owner->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('properties.edit', $property) }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Edit</a>
            @if(auth()->user()->hasRole(\App\Support\Roles::OWNER))
                <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('Delete this property? This cannot be undone.');">
                    @csrf @method('DELETE')
                    <button class="rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button>
                </form>
            @endif
        </div>
    </div>

    @if($property->notes)
        <div class="mb-6 rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600 shadow-sm">
            <span class="font-medium text-slate-700">Notes:</span> {{ $property->notes }}
        </div>
    @endif

    <section class="mb-8">
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Tenancy history</h2>
        <div class="space-y-2">
            @forelse($property->tenancies as $tenancy)
                <a href="{{ route('tenancies.show', $tenancy) }}" class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3 text-sm shadow-sm hover:border-sky-300">
                    <div>
                        <p class="font-medium text-slate-900">{{ $tenancy->tenant_name }}</p>
                        <p class="text-xs text-slate-500">{{ $tenancy->lease_start->format('d M Y') }} – {{ $tenancy->lease_end->format('d M Y') }}</p>
                    </div>
                    <x-status-badge :status="$tenancy->status" />
                </a>
            @empty
                <p class="text-sm text-slate-500">No tenancies recorded yet.</p>
            @endforelse
        </div>
    </section>

    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Maintenance requests</h2>
            <a href="{{ route('maintenance.create', ['property_id' => $property->id]) }}" class="text-sm font-medium text-sky-700 hover:text-sky-800">+ Log request</a>
        </div>
        <div class="space-y-2">
            @forelse($property->maintenanceRequests as $request)
                <a href="{{ route('maintenance.show', $request) }}" class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3 text-sm shadow-sm hover:border-sky-300">
                    <div>
                        <p class="font-medium text-slate-900">{{ \App\Models\MaintenanceRequest::CATEGORIES[$request->category] }}</p>
                        <p class="text-xs text-slate-500">{{ $request->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-urgency-badge :urgency="$request->urgency" />
                        <x-status-badge :status="$request->status" />
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-500">No maintenance requests yet.</p>
            @endforelse
        </div>
    </section>
</x-layout>
