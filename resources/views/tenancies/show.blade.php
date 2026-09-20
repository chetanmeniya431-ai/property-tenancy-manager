<x-layout title="Tenancy">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">{{ $tenancy->tenant_name }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                <a href="{{ route('properties.show', $tenancy->property) }}" class="hover:text-sky-700">{{ $tenancy->property->fullAddress() }}</a>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.dispute-export', $tenancy) }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Generate Dispute Report
            </a>
            <a href="{{ route('tenancies.edit', $tenancy) }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Edit</a>
            @if(auth()->user()->hasRole(\App\Support\Roles::OWNER))
                <form method="POST" action="{{ route('tenancies.destroy', $tenancy) }}" onsubmit="return confirm('Delete this tenancy record? This cannot be undone.');">
                    @csrf @method('DELETE')
                    <button class="rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Status</p>
            <div class="mt-1"><x-status-badge :status="$tenancy->status" /></div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Lease term</p>
            <p class="mt-1 text-sm font-medium text-slate-900">{{ $tenancy->lease_start->format('d M Y') }} – {{ $tenancy->lease_end->format('d M Y') }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Rent</p>
            <p class="mt-1 text-sm font-medium text-slate-900">£{{ number_format($tenancy->monthly_rent, 2) }} / month (due day {{ $tenancy->payment_due_day }})</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Deposit held</p>
            <p class="mt-1 text-sm font-medium text-slate-900">£{{ number_format($tenancy->deposit_amount, 2) }}</p>
        </div>
    </div>

    <div class="mb-8 grid gap-4 lg:grid-cols-2">
        @livewire('lease-upload', ['tenancy' => $tenancy], key('lease-upload-'.$tenancy->id))
        @livewire('lease-assistant', ['tenancy' => $tenancy], key('lease-assistant-'.$tenancy->id))
    </div>

    <section class="mb-8">
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Rent payments</h2>
        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Date</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Amount</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Method</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tenancy->rentPayments->take(12) as $payment)
                        <tr>
                            <td class="px-4 py-2 text-slate-600">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="px-4 py-2 text-slate-600">£{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $payment->method }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ $payment->notes }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-3 text-slate-500">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Maintenance requests</h2>
            <a href="{{ route('maintenance.create', ['tenancy_id' => $tenancy->id]) }}" class="text-sm font-medium text-sky-700 hover:text-sky-800">+ Log request</a>
        </div>
        <div class="space-y-2">
            @forelse($tenancy->maintenanceRequests as $request)
                <a href="{{ route('maintenance.show', $request) }}" class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3 text-sm shadow-sm hover:border-sky-300">
                    <p class="font-medium text-slate-900">{{ \App\Models\MaintenanceRequest::CATEGORIES[$request->category] }}</p>
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
