<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('tenancy_id')->nullable()->constrained('tenancies');
            $table->text('description');
            $table->json('attachments')->nullable();
            $table->string('reported_by_name');
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users');
            $table->foreignId('contractor_id')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->unsignedSmallInteger('sla_hours');
            $table->boolean('sla_breached')->default(false);
            $table->vector('embedding', 768)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE maintenance_requests ADD COLUMN category maintenance_category_enum NOT NULL");
        DB::statement("ALTER TABLE maintenance_requests ADD COLUMN urgency maintenance_urgency_enum NOT NULL");
        DB::statement("ALTER TABLE maintenance_requests ADD COLUMN status maintenance_status_enum NOT NULL DEFAULT 'logged'");

        DB::statement('CREATE INDEX maintenance_requests_embedding_hnsw ON maintenance_requests USING hnsw (embedding vector_cosine_ops)');
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->index('property_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
