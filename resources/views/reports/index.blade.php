<x-layout title="Reports">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Reports</h1>
    <p class="mb-4 text-sm text-slate-500">Generate a timestamped dispute evidence pack for any tenancy — full lease summary, maintenance history, signal events, and rent payment log.</p>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Tenant</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Property</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($tenancies as $tenancy)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $tenancy->tenant_name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $tenancy->property->fullAddress() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('reports.dispute-export', $tenancy) }}" class="text-sm font-medium text-sky-700 hover:text-sky-800">
                                Generate Dispute Report →
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
