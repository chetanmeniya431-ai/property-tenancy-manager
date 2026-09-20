<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('condition_key')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE signals ADD COLUMN severity signal_severity_enum NOT NULL");

        Schema::create('signal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signal_id')->constrained('signals');
            $table->foreignId('property_id')->nullable()->constrained('properties');
            $table->foreignId('tenancy_id')->nullable()->constrained('tenancies');
            $table->foreignId('request_id')->nullable()->constrained('maintenance_requests');
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_events');
        Schema::dropIfExists('signals');
    }
};
