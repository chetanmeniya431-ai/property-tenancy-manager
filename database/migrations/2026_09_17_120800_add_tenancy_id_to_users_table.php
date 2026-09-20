<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Not in the original schema sketch, but required to scope the Tenant role
 * to "their own property / their own requests" per the roles spec — a
 * Tenant user account must know which tenancy it represents.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenancy_id')->nullable()->after('id')->constrained('tenancies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenancy_id');
        });
    }
};
