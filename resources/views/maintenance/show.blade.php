@php
    $user = auth()->user();
    $isStaff = $user->hasAnyRole(\App\Support\Roles::STAFF);
    $isAssignedContractor = $request->contractor_id === $user->id;
    $canSeeFinancials = $user->hasAnyRole(\App\Support\Roles::BACK_OFFICE);
@endphp
<x-layout title="Maintenance request">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">{{ \App\Models\MaintenanceRequest::CATEGORIES[$request->category] }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $request->property->fullAddress() }}</p>
        </div>
        <div class="flex items-center gap-2">
            <x-urgency-badge :urgency="$request->urgency" />
            <x-status-badge :status="$request->status" />
        </div>
    </div>

    @if(session('obligation_note'))
        <div class="mb-6 rounded-md border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            <span class="font-medium">Lease check:</span> {{ session('obligation_note') }}
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:col-span-2">
            <p class="text-sm text-slate-700">{{ $request->description }}</p>
            <p class="mt-2 text-xs text-slate-500">
                Reported by {{ $request->reported_by_name }} · {{ $request->created_at->diffForHumans() }}
                @if($request->tenancy) · Tenancy: <a href="{{ route('tenancies.show', $request->tenancy) }}" class="text-sky-700 hover:underline">{{ $request->tenancy->tenant_name }}</a> @endif
            </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">SLA target</p>
            <p class="mt-1 text-sm font-medium text-slate-900">{{ $request->sla_hours }}h — due {{ $request->slaDeadline()->format('d M Y H:i') }}</p>
            @if($request->isSlaBreached())
                <p class="mt-1 text-xs font-medium text-red-600">SLA breached</p>
            @endif
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Contractor</p>
            <p class="mt-1 text-sm font-medium text-slate-900">{{ $request->contractor->name ?? 'Not yet assigned' }}</p>
        </div>
    </div>

    <div class="mb-8 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Actions</h2>
        <div class="flex flex-wrap gap-3">
            @if($isStaff && $request->status === 'logged')
                <form method="POST" action="{{ route('maintenance.assign', $request) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="contractor_id" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                        <option value="">Assign contractor…</option>
                        @foreach($contractors as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-md bg-sky-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-sky-500">Assign</button>
                </form>
            @endif

            @if($isAssignedContractor && $request->status === 'assigned')
                <form method="POST" action="{{ route('maintenance.start', $request) }}">
                    @csrf
                    <button class="rounded-md bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-400">Start work</button>
                </form>
            @endif

            @if($isAssignedContractor && $request->status === 'in_progress')
                <form method="POST" action="{{ route('maintenance.resolve', $request) }}">
                    @csrf
                    <button class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-500">Mark resolved</button>
                </form>
            @endif

            @if($isStaff && $request->status === 'resolved')
                <form method="POST" action="{{ route('maintenance.close', $request) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="text" name="resolution_note" placeholder="Resolution note" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    @if($canSeeFinancials)
                        <input type="number" step="0.01" name="cost" placeholder="Cost £" class="w-28 rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    @endif
                    <button class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Close</button>
                </form>
            @endif

            @if(in_array($request->status, ['resolved', 'closed']) && ($isStaff || ($user->hasRole(\App\Support\Roles::TENANT) && $request->tenancy_id === $user->tenancy_id)))
                <form method="POST" action="{{ route('maintenance.reopen', $request) }}">
                    @csrf
                    <button class="rounded-md border border-orange-300 bg-white px-3 py-1.5 text-sm font-medium text-orange-700 hover:bg-orange-50">Reopen — issue not fixed</button>
                </form>
            @endif

            @if(! $isStaff && ! $isAssignedContractor && ! ($user->hasRole(\App\Support\Roles::TENANT) && $request->tenancy_id === $user->tenancy_id))
                <p class="text-sm text-slate-400">No actions available.</p>
            @endif
        </div>

        @if($request->resolution_note)
            <p class="mt-3 text-sm text-slate-600"><span class="font-medium">Resolution note:</span> {{ $request->resolution_note }}</p>
        @endif
        @if($canSeeFinancials && $request->cost)
            <p class="mt-1 text-sm text-slate-600"><span class="font-medium">Cost:</span> £{{ number_format($request->cost, 2) }}</p>
        @endif
    </div>

    <section>
        <h2 class="mb-3 text-sm font-semibold text-slate-700">History</h2>
        <ol class="space-y-3 border-l border-slate-200 pl-4">
            @foreach($request->statusHistory as $entry)
                <li>
                    <p class="text-sm text-slate-700">
                        <span class="font-medium">{{ \App\Models\MaintenanceRequest::STATUSES[$entry->new_status] ?? $entry->new_status }}</span>
                        by {{ $entry->changedBy->name ?? 'System' }}
                    </p>
                    <p class="text-xs text-slate-400">{{ $entry->created_at->format('d M Y H:i') }}</p>
                    @if($entry->note)
                        <p class="mt-0.5 text-xs text-slate-500">{{ $entry->note }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    </section>
</x-layout>
