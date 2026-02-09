<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_soil_references', function (Blueprint $table) {
            $table->string('zip_code', 10)->primary();
            $table->char('state_code', 2)->index();
            $table->string('city');
            $table->string('county')->nullable();
            $table->string('timezone')->nullable();
            
            $table->decimal('latitude', 10, 6)->nullable()->index();
            $table->decimal('longitude', 10, 6)->nullable()->index();

            $table->string('climate_zone', 50)->nullable(); 
            $table->integer('frost_free_days')->nullable();
            
            $table->string('dominant_soil_texture')->nullable();
            $table->string('drainage_class')->nullable();
            $table->decimal('ph_mean', 3, 1)->nullable();
            $table->decimal('organic_matter_pct', 5, 2)->nullable();
            $table->integer('cec_meq')->nullable();

            $table->string('drought_risk', 50)->nullable();
            $table->string('compaction_risk', 50)->nullable();
            $table->string('n_leaching_risk', 50)->nullable();
            $table->string('disease_pressure', 50)->nullable();

            $table->json('monthly_temp_data')->nullable();
            $table->json('monthly_rainfall_data')->nullable();
            $table->json('growth_potential_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_soil_references');
    }
};