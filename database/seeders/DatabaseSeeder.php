<?php

namespace Database\Seeders;

use App\Services\Signals\SignalsEngine;
use App\Support\Roles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed synthetic demo data for Hartwell Property Management.
     *
     * Each sub-seeder's run() returns the rows it created so later seeders
     * can wire up relationships (property -> tenancy -> maintenance
     * request) without re-querying — hence these are instantiated and
     * called directly rather than via Seeder::call(), which discards the
     * return value.
     */
    public function run(): void
    {
        (new RoleSeeder)->run();
        (new SignalSeeder)->run();

        $users = (new UserSeeder)->run();
        $owner = $users[Roles::OWNER];

        $properties = (new PropertySeeder)->run($owner);

        $tenancies = (new TenancySeeder)->run($properties, $owner);

        // Link the seeded Tenant login to the tenancy it represents (property 0).
        $users[Roles::TENANT]->forceFill(['tenancy_id' => $tenancies[0]->id])->save();

        (new RentPaymentSeeder)->run($tenancies, $users[Roles::MANAGER]);

        (new MaintenanceRequestSeeder)->run($properties, $tenancies, $users);

        $this->command?->info('Evaluating signals against seeded data...');
        $fired = app(SignalsEngine::class)->run();
        foreach ($fired as $key => $count) {
            $this->command?->line("  {$key}: {$count} event(s)");
        }
    }
}
