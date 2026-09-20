<x-layout title="My Tenancy">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">My tenancy</h1>
        @if($tenancy)
            <a href="{{ route('maintenance.create') }}" class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">
                Report an issue
            </a>
        @endif
    </div>

    @if(! $tenancy)
        <p class="text-sm text-slate-500">Your account is not linked to a tenancy yet — contact your property manager.</p>
    @else
        <div class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold text-slate-900">{{ $tenancy->property->fullAddress() }}</p>
            <p class="mt-1 text-xs text-slate-500">Lease: {{ $tenancy->lease_start->format('d M Y') }} – {{ $tenancy->lease_end->format('d M Y') }}</p>
            <p class="mt-1 text-xs text-slate-500">Next rent due: {{ $tenancy->nextRentDueDate()->format('d M Y') }} (£{{ number_format($tenancy->monthly_rent, 2) }})</p>
            <div class="mt-2"><x-status-badge :status="$tenancy->status" /></div>
        </div>

        <h2 class="mb-3 text-sm font-semibold text-slate-700">My maintenance requests</h2>
        @if($requests->isEmpty())
            <p class="text-sm text-slate-500">You haven't reported any issues yet.</p>
        @else
            <div class="space-y-3">
                @foreach($requests as $request)
                    <a href="{{ route('maintenance.show', $request) }}" class="block rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-sky-300">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-900">{{ \App\Models\MaintenanceRequest::CATEGORIES[$request->category] }}</p>
                            <x-urgency-badge :urgency="$request->urgency" />
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $request->description }}</p>
                        <div class="mt-2"><x-status-badge :status="$request->status" /></div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</x-layout>
