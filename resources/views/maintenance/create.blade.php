<x-layout title="Log maintenance request">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Log a maintenance request</h1>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('maintenance.store') }}" x-data="{ propertyId: '{{ old('property_id', $selectedPropertyId ?? '') }}' }">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Property</label>
                    <select name="property_id" x-model="propertyId" @if($properties->count() === 1) disabled @endif
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        @foreach($properties as $p)
                            <option value="{{ $p->id }}" @selected(old('property_id', $selectedPropertyId ?? '') == $p->id)>{{ $p->fullAddress() }}</option>
                        @endforeach
                    </select>
                    @if($properties->count() === 1)
                        <input type="hidden" name="property_id" value="{{ $properties->first()->id }}">
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tenancy (optional)</label>
                    <select name="tenancy_id" @if($tenancies->count() === 1) disabled @endif
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        <option value="">— None —</option>
                        @foreach($tenancies as $t)
                            <option value="{{ $t->id }}" @selected(old('tenancy_id', $selectedTenancyId ?? '') == $t->id)>{{ $t->tenant_name }}</option>
                        @endforeach
                    </select>
                    @if($tenancies->count() === 1)
                        <input type="hidden" name="tenancy_id" value="{{ $tenancies->first()->id }}">
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Category</label>
                    <select name="category" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        @foreach(\App\Models\MaintenanceRequest::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Urgency</label>
                    <select name="urgency" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        @foreach(\App\Models\MaintenanceRequest::URGENCIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('urgency') === $key)>{{ $label }} ({{ \App\Models\MaintenanceRequest::SLA_HOURS[$key] }}h SLA)</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" rows="4" required
                              class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('maintenance.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">Log request</button>
            </div>
        </form>
    </div>
</x-layout>
