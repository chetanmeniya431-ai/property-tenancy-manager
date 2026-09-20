<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('postcode');
            $table->unsignedSmallInteger('bedrooms')->nullable();
            $table->decimal('floor_area_sqm', 8, 2)->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE properties ADD COLUMN property_type property_type_enum NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
