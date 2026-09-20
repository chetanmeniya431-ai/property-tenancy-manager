<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use App\Services\Maintenance\MaintenanceRequestService;
use App\Support\Roles;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = MaintenanceRequest::with(['property', 'tenancy', 'contractor'])->orderByDesc('created_at');

        if ($user->hasRole(Roles::CONTRACTOR)) {
            $query->where('contractor_id', $user->id);
        } elseif ($user->hasRole(Roles::TENANT)) {
            $query->where('tenancy_id', $user->tenancy_id);
        }

        return view('maintenance.index', ['requests' => $query->get()]);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(Roles::TENANT)) {
            $tenancy = $user->tenancy;
            abort_if(! $tenancy, 403, 'Your account is not linked to a tenancy.');

            return view('maintenance.create', [
                'properties' => collect([$tenancy->property]),
                'tenancies' => collect([$tenancy]),
                'selectedPropertyId' => $tenancy->property_id,
                'selectedTenancyId' => $tenancy->id,
            ]);
        }

        return view('maintenance.create', [
            'properties' => Property::orderBy('address_line1')->get(),
            'tenancies' => Tenancy::orderByDesc('created_at')->get(),
            'selectedPropertyId' => $request->query('property_id'),
            'selectedTenancyId' => $request->query('tenancy_id'),
        ]);
    }

    public function store(Request $request, MaintenanceRequestService $service)
    {
        $user = $request->user();

        $data = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'tenancy_id' => ['nullable', 'exists:tenancies,id'],
            'category' => ['required', 'in:'.implode(',', array_keys(MaintenanceRequest::CATEGORIES))],
            'urgency' => ['required', 'in:'.implode(',', array_keys(MaintenanceRequest::URGENCIES))],
            'description' => ['required', 'string'],
        ]);

        if ($user->hasRole(Roles::TENANT)) {
            abort_if($data['tenancy_id'] != $user->tenancy_id, 403);
        }

        $data['reported_by_name'] = $user->name;
        $data['reported_by_user_id'] = $user->id;

        $maintenanceRequest = $service->create($data, $user);

        $note = $service->obligationNote($maintenanceRequest);

        return redirect()->route('maintenance.show', $maintenanceRequest)
            ->with('status', 'Maintenance request logged.')
            ->with('obligation_note', $note);
    }

    public function show(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeView($request->user(), $maintenanceRequest);

        $maintenanceRequest->load(['property', 'tenancy', 'contractor', 'statusHistory.changedBy']);

        $contractors = User::role(Roles::CONTRACTOR)->get();

        return view('maintenance.show', [
            'request' => $maintenanceRequest,
            'contractors' => $contractors,
        ]);
    }

    public function assign(Request $request, MaintenanceRequest $maintenanceRequest, MaintenanceRequestService $service)
    {
        abort_unless($request->user()->hasAnyRole(Roles::STAFF), 403);

        $data = $request->validate(['contractor_id' => ['required', 'exists:users,id']]);
        $contractor = User::findOrFail($data['contractor_id']);

        $service->assignContractor($maintenanceRequest, $contractor, $request->user());

        return back()->with('status', 'Contractor assigned and notified.');
    }

    public function start(Request $request, MaintenanceRequest $maintenanceRequest, MaintenanceRequestService $service)
    {
        abort_unless($maintenanceRequest->contractor_id === $request->user()->id, 403);

        $service->startProgress($maintenanceRequest, $request->user());

        return back()->with('status', 'Marked in progress.');
    }

    public function resolve(Request $request, MaintenanceRequest $maintenanceRequest, MaintenanceRequestService $service)
    {
        abort_unless($maintenanceRequest->contractor_id === $request->user()->id, 403);

        $service->markResolved($maintenanceRequest, $request->user());

        return back()->with('status', 'Marked resolved. A manager can now close it.');
    }

    public function close(Request $request, MaintenanceRequest $maintenanceRequest, MaintenanceRequestService $service)
    {
        abort_unless($request->user()->hasAnyRole(Roles::STAFF), 403);

        $data = $request->validate([
            'resolution_note' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $service->close($maintenanceRequest, $request->user(), $data['resolution_note'] ?? null, $data['cost'] ?? null);

        return back()->with('status', 'Request closed.');
    }

    public function reopen(Request $request, MaintenanceRequest $maintenanceRequest, MaintenanceRequestService $service)
    {
        $user = $request->user();
        $canReopen = $user->hasAnyRole(Roles::STAFF)
            || ($user->hasRole(Roles::TENANT) && $maintenanceRequest->tenancy_id === $user->tenancy_id);

        abort_unless($canReopen, 403);

        $data = $request->validate(['note' => ['nullable', 'string']]);

        $service->reopen($maintenanceRequest, $user, $data['note'] ?? 'Reopened — issue reported again.');

        return back()->with('status', 'Request reopened.');
    }

    protected function authorizeView($user, MaintenanceRequest $maintenanceRequest): void
    {
        if ($user->hasAnyRole(Roles::STAFF)) {
            return;
        }

        if ($user->hasRole(Roles::CONTRACTOR) && $maintenanceRequest->contractor_id === $user->id) {
            return;
        }

        if ($user->hasRole(Roles::TENANT) && $maintenanceRequest->tenancy_id === $user->tenancy_id) {
            return;
        }

        abort(403);
    }
}
