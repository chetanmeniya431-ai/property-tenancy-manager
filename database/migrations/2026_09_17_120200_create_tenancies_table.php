<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('tenant_name');
            $table->string('tenant_email');
            $table->string('tenant_phone')->nullable();
            $table->date('lease_start');
            $table->date('lease_end');
            $table->decimal('monthly_rent', 10, 2);
            $table->unsignedTinyInteger('payment_due_day');
            $table->decimal('deposit_amount', 10, 2);
            $table->string('lease_file_path')->nullable();
            $table->timestamp('lease_embedded_at')->nullable();
            $table->text('end_reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE tenancies ADD COLUMN status tenancy_status_enum NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        Schema::dropIfExists('tenancies');
    }
};
