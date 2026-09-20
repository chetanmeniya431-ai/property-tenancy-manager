<x-layout title="Properties">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Properties</h1>
        <a href="{{ route('properties.create') }}" class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">
            + Add property
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Address</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Type</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-500">Owner</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($properties as $property)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('properties.show', $property) }}" class="font-medium text-sky-700 hover:underline">
                                {{ $property->fullAddress() }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ \App\Models\Property::TYPES[$property->property_type] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $property->owner->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
