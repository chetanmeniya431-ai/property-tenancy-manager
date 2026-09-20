<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::with('owner')->orderBy('address_line1')->get();

        return view('properties.index', ['properties' => $properties]);
    }

    public function create()
    {
        $owners = User::role(Roles::OWNER)->get();

        return view('properties.create', ['owners' => $owners]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $property = Property::create($data);

        return redirect()->route('properties.show', $property)->with('status', 'Property created.');
    }

    public function show(Property $property)
    {
        $property->load(['tenancies' => fn ($q) => $q->orderByDesc('lease_start'), 'maintenanceRequests' => fn ($q) => $q->orderByDesc('created_at')]);

        return view('properties.show', ['property' => $property]);
    }

    public function edit(Property $property)
    {
        $owners = User::role(Roles::OWNER)->get();

        return view('properties.edit', ['property' => $property, 'owners' => $owners]);
    }

    public function update(Request $request, Property $property)
    {
        $property->update($this->validated($request));

        return redirect()->route('properties.show', $property)->with('status', 'Property updated.');
    }

    public function destroy(Request $request, Property $property)
    {
        if (! $request->user()->hasRole(Roles::OWNER)) {
            abort(403, 'Only the Property Owner can delete a property.');
        }

        $property->delete();

        return redirect()->route('properties.index')->with('status', 'Property deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
            'property_type' => ['required', 'in:'.implode(',', array_keys(Property::TYPES))],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'floor_area_sqm' => ['nullable', 'numeric', 'min:0'],
            'owner_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['property_type'] === 'commercial_unit') {
            $data['bedrooms'] = null;
        } else {
            $data['floor_area_sqm'] = null;
        }

        return $data;
    }
}
