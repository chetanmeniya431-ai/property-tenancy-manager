<x-layout title="Tenancies">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Tenancies</h1>
        <a href="{{ route('tenancies.create') }}" class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">
            + Add tenancy
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Tenant</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Property</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Lease term</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Rent</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($tenancies as $tenancy)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('tenancies.show', $tenancy) }}" class="font-medium text-sky-700 hover:underline">
                                {{ $tenancy->tenant_name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $tenancy->property->fullAddress() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $tenancy->lease_start->format('d M Y') }} – {{ $tenancy->lease_end->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">£{{ number_format($tenancy->monthly_rent, 2) }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$tenancy->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
