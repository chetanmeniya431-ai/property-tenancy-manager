<?php

namespace Database\Seeders;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceStatusHistory;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use App\Services\Ollama\OllamaClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MaintenanceRequestSeeder extends Seeder
{
    /**
     * @param  array<int, Property>  $properties  indexed 0..7
     * @param  array<int, Tenancy>  $tenancies  indexed 0..9 as returned by TenancySeeder
     * @param  array<string, User>  $users  keyed by role
     */
    public function run(array $properties, array $tenancies, array $users): void
    {
        $ollama = app(OllamaClient::class);
        $contractor = $users[\App\Support\Roles::CONTRACTOR];
        $coordinator = $users[\App\Support\Roles::MAINTENANCE_COORDINATOR];
        $tenantUser = $users[\App\Support\Roles::TENANT];

        // property index => active/notice tenancy index
        $tenancyForProperty = [0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7];

        $now = Carbon::now();

        $defs = [
            // --- 5 "logged" (not yet assigned) ---
            [
                'property' => 0, 'category' => 'heating_cooling', 'urgency' => 'urgent', 'status' => 'logged',
                'description' => 'Boiler is losing pressure again and the hot water keeps cutting out overnight.',
                'created_ago_hours' => 36, 'reporter' => 'tenant',
            ],
            [
                'property' => 3, 'category' => 'electrical', 'urgency' => 'emergency', 'status' => 'logged',
                'description' => 'Sparking from the hallway light switch — looks unsafe, needs immediate attention.',
                'created_ago_hours' => 30, 'reporter' => 'tenant', // > 24h old, still logged -> SLA breach + emergency-open signal
            ],
            [
                'property' => 4, 'category' => 'appliances', 'urgency' => 'routine', 'status' => 'logged',
                'description' => 'Dishwasher is not draining fully at the end of the cycle.',
                'created_ago_hours' => 20, 'reporter' => 'tenant',
            ],
            [
                'property' => 1, 'category' => 'pest_control', 'urgency' => 'routine', 'status' => 'logged',
                'description' => 'Tenant has noticed a few ants near the kitchen skirting board.',
                'created_ago_hours' => 12, 'reporter' => 'tenant',
            ],
            [
                'property' => 6, 'category' => 'cleaning', 'urgency' => 'routine', 'status' => 'logged',
                'description' => 'Communal hallway carpet needs a deep clean after a spill.',
                'created_ago_hours' => 8, 'reporter' => 'coordinator',
            ],

            // --- 6 "assigned to contractor" ---
            [
                'property' => 2, 'category' => 'plumbing', 'urgency' => 'urgent', 'status' => 'assigned',
                'description' => 'Slow-draining bathroom sink, water backs up when the bath is also draining.',
                'created_ago_hours' => 60, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 5, 'category' => 'structural', 'urgency' => 'routine', 'status' => 'assigned',
                'description' => 'Small crack appearing in the plaster above the living room window.',
                'created_ago_hours' => 90, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 7, 'category' => 'electrical', 'urgency' => 'urgent', 'status' => 'assigned',
                'description' => 'One of the office lighting circuits keeps tripping the breaker.',
                'created_ago_hours' => 40, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 0, 'category' => 'appliances', 'urgency' => 'routine', 'status' => 'assigned',
                'description' => 'Extractor fan in the bathroom is very noisy and not extracting well.',
                'created_ago_hours' => 100, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 4, 'category' => 'heating_cooling', 'urgency' => 'urgent', 'status' => 'assigned',
                'description' => 'Radiator in the second bedroom is cold at the top — needs bleeding or a new valve.',
                'created_ago_hours' => 50, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 3, 'category' => 'plumbing', 'urgency' => 'routine', 'status' => 'assigned',
                'description' => 'Dripping tap in the kitchen, slow but constant.',
                'created_ago_hours' => 70, 'reporter' => 'tenant', 'contractor' => true,
            ],

            // --- 6 "in progress" ---
            [
                'property' => 1, 'category' => 'structural', 'urgency' => 'routine', 'status' => 'in_progress',
                'description' => 'Sealant around the bathroom window has perished and needs replacing.',
                'created_ago_hours' => 150, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 5, 'category' => 'plumbing', 'urgency' => 'urgent', 'status' => 'in_progress',
                'description' => 'Toilet cistern is not refilling properly after flushing.',
                'created_ago_hours' => 120, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 6, 'category' => 'heating_cooling', 'urgency' => 'urgent', 'status' => 'in_progress',
                'description' => 'Central heating thermostat is unresponsive, boiler running constantly.',
                'created_ago_hours' => 96, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 7, 'category' => 'cleaning', 'urgency' => 'routine', 'status' => 'in_progress',
                'description' => 'Deep clean requested for the office carpet tiles near the entrance.',
                'created_ago_hours' => 80, 'reporter' => 'coordinator', 'contractor' => true,
            ],
            [
                'property' => 2, 'category' => 'appliances', 'urgency' => 'routine', 'status' => 'in_progress',
                'description' => 'Oven is not heating evenly, one side browns faster than the other.',
                'created_ago_hours' => 110, 'reporter' => 'tenant', 'contractor' => true,
            ],
            [
                'property' => 4, 'category' => 'pest_control', 'urgency' => 'urgent', 'status' => 'in_progress',
                'description' => 'Signs of mice in the kitchen cupboards, tenant has removed food waste already.',
                'created_ago_hours' => 60, 'reporter' => 'tenant', 'contractor' => true,
            ],

            // --- 8 "resolved/closed", including the emergency-resolved case, the original boiler report, and 4 reopened ---
            [
                'property' => 5, 'category' => 'plumbing', 'urgency' => 'emergency', 'status' => 'closed',
                'description' => 'Burst pipe under the kitchen sink, water everywhere, needs an emergency plumber.',
                'created_ago_hours' => 200, 'reporter' => 'tenant', 'contractor' => true,
                'resolved_ago_hours' => 198, 'closed_ago_hours' => 190,
                'resolution_note' => 'Pipe joint replaced under the sink, area dried out and tested for 24h.', 'cost' => 180,
            ],
            [
                'property' => 0, 'category' => 'heating_cooling', 'urgency' => 'urgent', 'status' => 'closed',
                'description' => 'Boiler keeps cutting out and losing pressure, no hot water in the mornings.',
                'created_ago_hours' => 960, 'reporter' => 'tenant', 'contractor' => true,
                'resolved_ago_hours' => 900, 'closed_ago_hours' => 890,
                'resolution_note' => 'Repressurised system and replaced the expansion vessel.', 'cost' => 140,
            ],
            [
                'property' => 1, 'category' => 'electrical', 'urgency' => 'routine', 'status' => 'resolved',
                'description' => 'Socket in the living room stopped working.',
                'created_ago_hours' => 300, 'reporter' => 'tenant', 'contractor' => true,
                'resolved_ago_hours' => 250,
                'reopened' => true, 'reopen_ago_hours' => 200, 'reresolved_ago_hours' => 150,
            ],
            [
                'property' => 2, 'category' => 'plumbing', 'urgency' => 'urgent', 'status' => 'closed',
                'description' => 'Shower is not draining and water pools at the tenant\'s feet.',
                'created_ago_hours' => 400, 'reporter' => 'tenant', 'contractor' => true,
                'resolved_ago_hours' => 380, 'closed_ago_hours' => 360,
                'reopened' => true, 'reopen_ago_hours' => 320, 'reresolved_ago_hours' => 300,
                'resolution_note' => 'Cleared blockage, re-sealed shower tray.', 'cost' => 95,
            ],
            [
                'property' => 6, 'category' => 'appliances', 'urgency' => 'routine', 'status' => 'resolved',
                'description' => 'Washing machine drum was not spinning correctly.',
                'created_ago_hours' => 250, 'reporter' => 'tenant', 'contractor' => true,
                'resolved_ago_hours' => 220,
                'reopened' => true, 'reopen_ago_hours' => 190, 'reresolved_ago_hours' => 100,
            ],
            [
                'property' => 3, 'category' => 'heating_cooling', 'urgency' => 'urgent', 'status' => 'closed',
                'description' => 'No heating in any room, thermostat display is blank.',
                'created_ago_hours' => 500, 'reporter' => 'tenant', 'contractor' => true,
                'resolved_ago_hours' => 480, 'closed_ago_hours' => 460,
                'reopened' => true, 'reopen_ago_hours' => 400, 'reresolved_ago_hours' => 380,
                'resolution_note' => 'Replaced thermostat batteries and reset the boiler control board.', 'cost' => 60,
            ],
            [
                'property' => 7, 'category' => 'structural', 'urgency' => 'routine', 'status' => 'closed',
                'description' => 'Ceiling tile in the office corridor had come loose.',
                'created_ago_hours' => 600, 'reporter' => 'coordinator', 'contractor' => true,
                'resolved_ago_hours' => 580, 'closed_ago_hours' => 570,
                'resolution_note' => 'Tile re-seated and clipped in.', 'cost' => 40,
            ],
            [
                'property' => 4, 'category' => 'cleaning', 'urgency' => 'routine', 'status' => 'resolved',
                'description' => 'End-of-tenancy clean requested for the hallway and stairwell.',
                'created_ago_hours' => 150, 'reporter' => 'coordinator', 'contractor' => true,
                'resolved_ago_hours' => 100,
            ],
        ];

        foreach ($defs as $def) {
            $property = $properties[$def['property']];
            $tenancy = isset($tenancyForProperty[$def['property']]) ? $tenancies[$tenancyForProperty[$def['property']]] : null;

            $createdAt = $now->copy()->subHours($def['created_ago_hours']);
            $reportedByUserId = $def['reporter'] === 'tenant' ? $tenantUser->id : null;
            $reportedByName = match ($def['reporter']) {
                'tenant' => $tenancy?->tenant_name ?? 'Tenant',
                'coordinator' => $coordinator->name,
                default => 'Property Manager',
            };

            $label = MaintenanceRequest::CATEGORIES[$def['category']];
            $embedding = $ollama->embed("{$label}: {$def['description']}");

            $request = MaintenanceRequest::create([
                'property_id' => $property->id,
                'tenancy_id' => $tenancy?->id,
                'category' => $def['category'],
                'urgency' => $def['urgency'],
                'description' => $def['description'],
                'attachments' => null,
                'reported_by_name' => $reportedByName,
                'reported_by_user_id' => $reportedByUserId,
                'status' => $def['status'],
                'contractor_id' => ! empty($def['contractor']) ? $contractor->id : null,
                'resolved_at' => isset($def['resolved_ago_hours']) ? $now->copy()->subHours($def['resolved_ago_hours']) : null,
                'closed_at' => isset($def['closed_ago_hours']) ? $now->copy()->subHours($def['closed_ago_hours']) : null,
                'resolution_note' => $def['resolution_note'] ?? null,
                'cost' => $def['cost'] ?? null,
                'sla_hours' => MaintenanceRequest::SLA_HOURS[$def['urgency']],
                'sla_breached' => false,
                'embedding' => $embedding,
            ]);

            $request->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            $this->history($request, null, 'logged', $coordinator, $createdAt);

            if (! empty($def['contractor']) && $def['status'] !== 'logged') {
                $this->history($request, 'logged', 'assigned', $coordinator, $createdAt->copy()->addHour());
            }

            if (in_array($def['status'], ['in_progress', 'resolved', 'closed'])) {
                $this->history($request, 'assigned', 'in_progress', $contractor, $createdAt->copy()->addHours(2));
            }

            if (isset($def['resolved_ago_hours'])) {
                $firstResolvedAt = $now->copy()->subHours($def['resolved_ago_hours']);
                $this->history($request, 'in_progress', 'resolved', $contractor, $firstResolvedAt);

                if (! empty($def['reopened'])) {
                    $reopenAt = $now->copy()->subHours($def['reopen_ago_hours']);
                    $this->history($request, 'resolved', 'in_progress', $tenantUser, $reopenAt, 'Tenant reported the same issue again.');

                    $reresolvedAt = $now->copy()->subHours($def['reresolved_ago_hours']);
                    $this->history($request, 'in_progress', 'resolved', $contractor, $reresolvedAt);

                    $request->forceFill(['resolved_at' => $reresolvedAt])->save();
                }
            }

            if (isset($def['closed_ago_hours'])) {
                $this->history($request, 'resolved', 'closed', $coordinator, $now->copy()->subHours($def['closed_ago_hours']));
            }
        }
    }

    protected function history(MaintenanceRequest $request, ?string $old, string $new, User $actor, Carbon $at, ?string $note = null): void
    {
        MaintenanceStatusHistory::create([
            'request_id' => $request->id,
            'old_status' => $old,
            'new_status' => $new,
            'changed_by' => $actor->id,
            'note' => $note,
            'created_at' => $at,
        ]);
    }
}
