@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Property</label>
        <select name="property_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @foreach($properties as $p)
                <option value="{{ $p->id }}" @selected(old('property_id', $tenancy->property_id ?? ($selectedPropertyId ?? '')) == $p->id)>{{ $p->fullAddress() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Tenant name</label>
        <input name="tenant_name" value="{{ old('tenant_name', $tenancy->tenant_name ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Tenant email</label>
        <input type="email" name="tenant_email" value="{{ old('tenant_email', $tenancy->tenant_email ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Tenant phone</label>
        <input name="tenant_phone" value="{{ old('tenant_phone', $tenancy->tenant_phone ?? '') }}"
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @foreach(\App\Models\Tenancy::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $tenancy->status ?? 'active') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Lease start</label>
        <input type="date" name="lease_start" value="{{ old('lease_start', isset($tenancy) ? $tenancy->lease_start->toDateString() : '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Lease end</label>
        <input type="date" name="lease_end" value="{{ old('lease_end', isset($tenancy) ? $tenancy->lease_end->toDateString() : '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Monthly rent (£)</label>
        <input type="number" step="0.01" name="monthly_rent" value="{{ old('monthly_rent', $tenancy->monthly_rent ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Payment due day (1–28)</label>
        <input type="number" min="1" max="28" name="payment_due_day" value="{{ old('payment_due_day', $tenancy->payment_due_day ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Deposit amount (£)</label>
        <input type="number" step="0.01" name="deposit_amount" value="{{ old('deposit_amount', $tenancy->deposit_amount ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">End reason (if ended / notice given)</label>
        <textarea name="end_reason" rows="2"
                  class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('end_reason', $tenancy->end_reason ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('tenancies.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
    <button type="submit" class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">Save</button>
</div>
