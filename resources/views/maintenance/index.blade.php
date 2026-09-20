<x-layout title="Maintenance">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Maintenance requests</h1>
        <a href="{{ route('maintenance.create') }}" class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">
            + Log request
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Property</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Category</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Urgency</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Logged</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">SLA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($requests as $request)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('maintenance.show', $request) }}" class="font-medium text-sky-700 hover:underline">
                                {{ $request->property->fullAddress() }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ \App\Models\MaintenanceRequest::CATEGORIES[$request->category] }}</td>
                        <td class="px-4 py-3"><x-urgency-badge :urgency="$request->urgency" /></td>
                        <td class="px-4 py-3"><x-status-badge :status="$request->status" /></td>
                        <td class="px-4 py-3 text-slate-500">{{ $request->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            @if($request->isSlaBreached())
                                <span class="font-medium text-red-600">Overdue</span>
                            @else
                                <span class="text-slate-400">On track</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
