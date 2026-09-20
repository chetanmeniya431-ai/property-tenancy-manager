<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // migrate:fresh drops tables but not standalone types, so these must
        // survive being re-run against a database that already has them.
        $this->createEnumIfMissing('property_type_enum', "'residential_flat', 'residential_house', 'commercial_unit', 'hmo'");
        $this->createEnumIfMissing('tenancy_status_enum', "'active', 'expired', 'notice_given', 'ended'");
        $this->createEnumIfMissing('maintenance_category_enum', "'plumbing', 'electrical', 'structural', 'heating_cooling', 'appliances', 'pest_control', 'cleaning', 'other'");
        $this->createEnumIfMissing('maintenance_urgency_enum', "'emergency', 'urgent', 'routine'");
        $this->createEnumIfMissing('maintenance_status_enum', "'logged', 'assigned', 'in_progress', 'resolved', 'closed'");
        $this->createEnumIfMissing('signal_severity_enum', "'low', 'medium', 'high', 'critical'");
    }

    protected function createEnumIfMissing(string $name, string $values): void
    {
        DB::statement("DO \$\$ BEGIN
            CREATE TYPE {$name} AS ENUM ({$values});
        EXCEPTION WHEN duplicate_object THEN NULL;
        END \$\$;");
    }

    public function down(): void
    {
        DB::statement('DROP TYPE IF EXISTS property_type_enum');
        DB::statement('DROP TYPE IF EXISTS tenancy_status_enum');
        DB::statement('DROP TYPE IF EXISTS maintenance_category_enum');
        DB::statement('DROP TYPE IF EXISTS maintenance_urgency_enum');
        DB::statement('DROP TYPE IF EXISTS maintenance_status_enum');
        DB::statement('DROP TYPE IF EXISTS signal_severity_enum');
    }
};
