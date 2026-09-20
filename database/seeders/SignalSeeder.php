<?php

namespace Database\Seeders;

use App\Models\Signal;
use Illuminate\Database\Seeder;

class SignalSeeder extends Seeder
{
    public function run(): void
    {
        $signals = [
            ['name' => 'Rent Overdue', 'condition_key' => 'rent_overdue', 'severity' => 'high'],
            ['name' => 'Lease Expiry — 60 Days', 'condition_key' => 'lease_expiry_60', 'severity' => 'medium'],
            ['name' => 'Lease Expiry — 30 Days', 'condition_key' => 'lease_expiry_30', 'severity' => 'high'],
            ['name' => 'Maintenance SLA Breached', 'condition_key' => 'maintenance_sla_breached', 'severity' => 'high'],
            ['name' => 'Recurring Issue at Property', 'condition_key' => 'recurring_issue_at_property', 'severity' => 'medium'],
            ['name' => 'Emergency Request Open 24h', 'condition_key' => 'emergency_request_open_24h', 'severity' => 'critical'],
            ['name' => 'Contractor Reopened Request', 'condition_key' => 'contractor_reopened_request', 'severity' => 'medium'],
            ['name' => 'Expired Tenancy — No Action', 'condition_key' => 'expired_tenancy_no_action', 'severity' => 'high'],
        ];

        foreach ($signals as $signal) {
            Signal::updateOrCreate(
                ['condition_key' => $signal['condition_key']],
                ['name' => $signal['name'], 'severity' => $signal['severity'], 'active' => true],
            );
        }
    }
}
