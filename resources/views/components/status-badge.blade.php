@php
    $colors = [
        'logged' => 'bg-slate-100 text-slate-700',
        'assigned' => 'bg-indigo-100 text-indigo-700',
        'in_progress' => 'bg-amber-100 text-amber-700',
        'resolved' => 'bg-emerald-100 text-emerald-700',
        'closed' => 'bg-slate-200 text-slate-600',
        'active' => 'bg-emerald-100 text-emerald-700',
        'expired' => 'bg-slate-200 text-slate-600',
        'notice_given' => 'bg-amber-100 text-amber-700',
        'ended' => 'bg-slate-200 text-slate-600',
    ];
    $labels = \App\Models\MaintenanceRequest::STATUSES + \App\Models\Tenancy::STATUSES;
@endphp
<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $colors[$status] ?? 'bg-slate-100 text-slate-700' }}">
    {{ $labels[$status] ?? $status }}
</span>
