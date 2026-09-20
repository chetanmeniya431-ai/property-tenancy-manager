<?php

namespace App\Http\Controllers;

use App\Models\Tenancy;
use App\Services\Reports\DisputeExportService;

class ReportController extends Controller
{
    public function index()
    {
        $tenancies = Tenancy::with('property')->orderByDesc('created_at')->get();

        return view('reports.index', ['tenancies' => $tenancies]);
    }

    public function disputeExport(Tenancy $tenancy, DisputeExportService $service)
    {
        $pdf = $service->build($tenancy);

        $filename = 'dispute-export-tenancy-'.$tenancy->id.'-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($filename);
    }
}
