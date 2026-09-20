@php
    $colors = [
        'emergency' => 'bg-red-100 text-red-700',
        'urgent' => 'bg-orange-100 text-orange-700',
        'routine' => 'bg-blue-100 text-blue-700',
    ];
@endphp
<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $colors[$urgency] ?? 'bg-slate-100 text-slate-700' }}">
    {{ \App\Models\MaintenanceRequest::URGENCIES[$urgency] ?? $urgency }}
</span>
