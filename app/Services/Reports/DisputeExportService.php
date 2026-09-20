<?php

namespace App\Services\Reports;

use App\Models\Tenancy;
use Barryvdh\DomPDF\Facade\Pdf;

class DisputeExportService
{
    public function build(Tenancy $tenancy): \Barryvdh\DomPDF\PDF
    {
        $tenancy->loadMissing([
            'property',
            'rentPayments.recorder',
            'maintenanceRequests.statusHistory.changedBy',
            'maintenanceRequests.contractor',
            'signalEvents.signal',
            'leaseChunks',
        ]);

        $leaseSummary = $tenancy->leaseChunks
            ->sortBy('chunk_index')
            ->pluck('chunk_text')
            ->implode(' ');

        $leaseSummary = implode(' ', array_slice(preg_split('/\s+/', $leaseSummary, -1, PREG_SPLIT_NO_EMPTY), 0, 500));

        $pdf = Pdf::loadView('reports.dispute-export', [
            'tenancy' => $tenancy,
            'leaseSummary' => $leaseSummary,
            'generatedAt' => now(),
        ]);

        return $pdf->setPaper('a4');
    }
}
