<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_soil_references', function (Blueprint $table) {
            $table->string('zip_code', 10)->primary()->comment('Indexed Primary Key');
            $table->char('state_code', 2);
            $table->string('city');
            $table->string('hardiness_zone')->nullable();
            $table->string('dominant_soil_texture')->nullable();
            $table->decimal('ph_mean', 3, 1)->nullable();
            
            $table->json('monthly_temp_data')->nullable();
            $table->json('monthly_rainfall_data')->nullable();
            $table->json('growth_potential_data')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('state_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_soil_references');
    }
};