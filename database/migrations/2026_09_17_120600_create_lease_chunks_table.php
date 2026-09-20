<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')->constrained('tenancies')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->text('chunk_text');
            $table->vector('embedding', 768);
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenancy_id');
        });

        DB::statement('CREATE INDEX lease_chunks_embedding_hnsw ON lease_chunks USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_chunks');
    }
};
