@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Address line 1</label>
        <input name="address_line1" value="{{ old('address_line1', $property->address_line1 ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Address line 2</label>
        <input name="address_line2" value="{{ old('address_line2', $property->address_line2 ?? '') }}"
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">City</label>
        <input name="city" value="{{ old('city', $property->city ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Postcode</label>
        <input name="postcode" value="{{ old('postcode', $property->postcode ?? '') }}" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
    </div>
    <div x-data="{ type: '{{ old('property_type', $property->property_type ?? 'residential_flat') }}' }">
        <label class="block text-sm font-medium text-slate-700">Property type</label>
        <select name="property_type" x-model="type"
                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @foreach(\App\Models\Property::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(old('property_type', $property->property_type ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="mt-3" x-show="type !== 'commercial_unit'">
            <label class="block text-sm font-medium text-slate-700">Bedrooms</label>
            <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', $property->bedrooms ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
        </div>
        <div class="mt-3" x-show="type === 'commercial_unit'">
            <label class="block text-sm font-medium text-slate-700">Floor area (m²)</label>
            <input type="number" step="0.01" name="floor_area_sqm" value="{{ old('floor_area_sqm', $property->floor_area_sqm ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Owner</label>
        <select name="owner_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @foreach($owners as $owner)
                <option value="{{ $owner->id }}" @selected(old('owner_id', $property->owner_id ?? '') == $owner->id)>{{ $owner->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea name="notes" rows="3"
                  class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('notes', $property->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('properties.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
    <button type="submit" class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">Save</button>
</div>
