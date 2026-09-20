<x-layout title="My Requests">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">My assigned requests</h1>

    @if($requests->isEmpty())
        <p class="text-sm text-slate-500">You have no open assigned requests right now.</p>
    @else
        <div class="space-y-3">
            @foreach($requests as $request)
                <a href="{{ route('maintenance.show', $request) }}" class="block rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-sky-300">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">{{ $request->property->fullAddress() }}</p>
                        <x-urgency-badge :urgency="$request->urgency" />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ \App\Models\MaintenanceRequest::CATEGORIES[$request->category] }} · Logged {{ $request->created_at->diffForHumans() }}</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $request->description }}</p>
                    <div class="mt-2"><x-status-badge :status="$request->status" /></div>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
