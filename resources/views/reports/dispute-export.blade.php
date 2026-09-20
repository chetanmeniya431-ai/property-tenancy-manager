<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
    h1 { font-size: 16px; margin-bottom: 2px; }
    h2 { font-size: 13px; margin-top: 16px; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    th, td { text-align: left; padding: 3px 6px; font-size: 10px; border-bottom: 1px solid #eee; }
    th { color: #555; }
    .meta { color: #666; font-size: 10px; }
    .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 9px; background: #f1f5f9; }
</style>
</head>
<body>
<h1>Tenancy Dispute Evidence Pack</h1>
<p class="meta">Generated {{ $generatedAt->format('d M Y H:i:s') }} — for use in tribunal or dispute resolution proceedings.</p>

<h2>Tenancy Details</h2>
<table>
    <tr><th>Property</th><td>{{ $tenancy->property->fullAddress() }}</td></tr>
    <tr><th>Tenant</th><td>{{ $tenancy->tenant_name }} ({{ $tenancy->tenant_email }})</td></tr>
    <tr><th>Lease period</th><td>{{ $tenancy->lease_start->format('d M Y') }} – {{ $tenancy->lease_end->format('d M Y') }}</td></tr>
    <tr><th>Monthly rent</th><td>£{{ number_format($tenancy->monthly_rent, 2) }} (due day {{ $tenancy->payment_due_day }})</td></tr>
    <tr><th>Deposit held</th><td>£{{ number_format($tenancy->deposit_amount, 2) }}</td></tr>
    <tr><th>Status</th><td>{{ \App\Models\Tenancy::STATUSES[$tenancy->status] }}</td></tr>
</table>

<h2>Lease Summary (first 500 words)</h2>
<p>{{ $leaseSummary ?: 'No lease document has been ingested for this tenancy.' }}</p>

<h2>Maintenance Request History</h2>
@forelse($tenancy->maintenanceRequests as $request)
    <p style="margin-top:8px;"><strong>#{{ $request->id }} — {{ \App\Models\MaintenanceRequest::CATEGORIES[$request->category] }}</strong>
        ({{ \App\Models\MaintenanceRequest::URGENCIES[$request->urgency] }}, SLA {{ $request->sla_hours }}h)
        — status: {{ \App\Models\MaintenanceRequest::STATUSES[$request->status] }}</p>
    <p class="meta">{{ $request->description }}</p>
    <table>
        <tr><th>Status change</th><th>Changed by</th><th>Date</th><th>Note</th></tr>
        @foreach($request->statusHistory as $entry)
            <tr>
                <td>{{ $entry->old_status ?? '—' }} → {{ $entry->new_status }}</td>
                <td>{{ $entry->changedBy->name ?? 'System' }}</td>
                <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                <td>{{ $entry->note }}</td>
            </tr>
        @endforeach
    </table>
    @if($request->resolution_note)
        <p class="meta">Resolution note: {{ $request->resolution_note }}@if($request->cost) — cost £{{ number_format($request->cost, 2) }}@endif</p>
    @endif
@empty
    <p class="meta">No maintenance requests recorded for this tenancy.</p>
@endforelse

<h2>Signal Events</h2>
<table>
    <tr><th>Signal</th><th>Severity</th><th>Triggered</th><th>Resolved</th><th>Note</th></tr>
    @forelse($tenancy->signalEvents as $event)
        <tr>
            <td>{{ $event->signal->name }}</td>
            <td>{{ ucfirst($event->signal->severity) }}</td>
            <td>{{ $event->triggered_at->format('d M Y H:i') }}</td>
            <td>{{ $event->resolved_at?->format('d M Y H:i') ?? 'Open' }}</td>
            <td>{{ $event->note }}</td>
        </tr>
    @empty
        <tr><td colspan="5">No signal events for this tenancy.</td></tr>
    @endforelse
</table>

<h2>Rent Payment Log</h2>
<table>
    <tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th>Recorded by</th></tr>
    @forelse($tenancy->rentPayments as $payment)
        <tr>
            <td>{{ $payment->payment_date->format('d M Y') }}</td>
            <td>£{{ number_format($payment->amount, 2) }}</td>
            <td>{{ $payment->method }}</td>
            <td>{{ $payment->reference }}</td>
            <td>{{ $payment->recorder->name ?? '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="5">No rent payments recorded.</td></tr>
    @endforelse
</table>

</body>
</html>
