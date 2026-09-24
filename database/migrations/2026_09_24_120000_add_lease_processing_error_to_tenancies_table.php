<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->text('lease_processing_error')->nullable()->after('lease_embedded_at');
            // The stored file is a randomized name (tenancy-{id}-{random}.pdf) —
            // these let the UI show what was actually uploaded and when.
            $table->string('lease_original_filename')->nullable()->after('lease_file_path');
            $table->timestamp('lease_uploaded_at')->nullable()->after('lease_original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->dropColumn(['lease_processing_error', 'lease_original_filename', 'lease_uploaded_at']);
        });
    }
};
