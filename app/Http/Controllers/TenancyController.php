<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Tenancy;
use App\Support\Roles;
use Illuminate\Http\Request;

class TenancyController extends Controller
{
    public function index()
    {
        $tenancies = Tenancy::with('property')->orderByDesc('created_at')->get();

        return view('tenancies.index', ['tenancies' => $tenancies]);
    }

    public function create(Request $request)
    {
        $properties = Property::orderBy('address_line1')->get();

        return view('tenancies.create', ['properties' => $properties, 'selectedPropertyId' => $request->query('property_id')]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'active';

        if ($data['status'] === 'active') {
            $hasActive = Tenancy::where('property_id', $data['property_id'])->where('status', 'active')->exists();
            if ($hasActive) {
                return back()->withErrors(['property_id' => 'This property already has an active tenancy.'])->withInput();
            }
        }

        $tenancy = Tenancy::create($data);

        return redirect()->route('tenancies.show', $tenancy)->with('status', 'Tenancy created.');
    }

    public function show(Tenancy $tenancy)
    {
        $tenancy->load(['property', 'rentPayments.recorder', 'maintenanceRequests', 'leaseChunks']);

        return view('tenancies.show', ['tenancy' => $tenancy]);
    }

    public function edit(Tenancy $tenancy)
    {
        $properties = Property::orderBy('address_line1')->get();

        return view('tenancies.edit', ['tenancy' => $tenancy, 'properties' => $properties]);
    }

    public function update(Request $request, Tenancy $tenancy)
    {
        $data = $this->validated($request);
        $tenancy->update($data);

        return redirect()->route('tenancies.show', $tenancy)->with('status', 'Tenancy updated.');
    }

    public function destroy(Request $request, Tenancy $tenancy)
    {
        if (! $request->user()->hasRole(Roles::OWNER)) {
            abort(403, 'Only the Property Owner can delete a tenancy record.');
        }

        $tenancy->delete();

        return redirect()->route('tenancies.index')->with('status', 'Tenancy deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_email' => ['required', 'email', 'max:255'],
            'tenant_phone' => ['nullable', 'string', 'max:50'],
            'lease_start' => ['required', 'date'],
            'lease_end' => ['required', 'date', 'after:lease_start'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'payment_due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(\App\Models\Tenancy::STATUSES))],
            'end_reason' => ['nullable', 'string'],
        ]);
    }
}
