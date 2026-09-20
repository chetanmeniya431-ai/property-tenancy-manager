<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\SignalEvent;
use App\Support\Roles;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole(Roles::CONTRACTOR)) {
            $requests = MaintenanceRequest::with(['property', 'tenancy'])
                ->where('contractor_id', $user->id)
                ->whereNotIn('status', ['closed'])
                ->orderBy('created_at')
                ->get();

            return view('dashboard.contractor', ['requests' => $requests]);
        }

        if ($user->hasRole(Roles::TENANT)) {
            $tenancy = $user->tenancy;
            $requests = $tenancy
                ? MaintenanceRequest::with('property')->where('tenancy_id', $tenancy->id)->orderByDesc('created_at')->get()
                : collect();

            return view('dashboard.tenant', ['tenancy' => $tenancy, 'requests' => $requests]);
        }

        // Property Owner / Property Manager / Maintenance Coordinator
        $properties = Property::with(['tenancies' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('address_line1')
            ->get()
            ->map(function (Property $property) {
                $property->open_maintenance_count = $property->maintenanceRequests()
                    ->whereNotIn('status', ['resolved', 'closed'])
                    ->count();
                $property->active_tenancy = $property->tenancies->first();

                return $property;
            });

        $signalEvents = SignalEvent::with(['signal', 'property', 'tenancy', 'request'])
            ->whereNull('resolved_at')
            ->orderByDesc('triggered_at')
            ->get();

        return view('dashboard.staff', [
            'properties' => $properties,
            'signalEvents' => $signalEvents,
        ]);
    }
}
