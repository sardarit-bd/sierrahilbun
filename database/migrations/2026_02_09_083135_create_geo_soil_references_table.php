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
            $table->string('type', 50)->nullable(); 
            $table->boolean('decommissioned')->default(0); 
            $table->string('primary_city'); 
            $table->text('acceptable_cities')->nullable(); 
            $table->text('unacceptable_cities')->nullable();
            $table->char('state', 2)->index(); 
            $table->string('county')->nullable(); 
            $table->string('timezone')->nullable(); 
            $table->string('area_codes')->nullable(); 
            $table->string('world_region')->nullable(); 
            $table->char('country', 2)->nullable();
            
            $table->decimal('latitude', 10, 6)->nullable()->index();
            $table->decimal('longitude', 10, 6)->nullable()->index(); 
            
            $table->string('climate_zone', 50)->nullable();
            $table->decimal('avg_annual_rain_in', 5, 2)->nullable();
            $table->integer('avg_july_high_F')->nullable();
            $table->integer('avg_jan_low_F')->nullable();
            $table->integer('frost_free_days')->nullable();
            
            $table->decimal('avg_ph', 4, 2)->nullable();
            $table->decimal('soil_ph_max', 4, 2)->nullable(); 
            $table->string('avg_ph_class')->nullable(); 
            $table->string('soil_ph_class')->nullable(); 
            
            $table->decimal('cec_meq_per_100g', 5, 1)->nullable(); 
            $table->string('cec_class')->nullable(); 
            
            $table->string('organic_matter_class')->nullable(); 
            $table->string('calcium_saturation_class')->nullable(); 
            $table->string('soil_texture_class')->nullable(); 
            $table->string('drainage_class')->nullable(); 
            
            $table->string('compaction_risk')->nullable(); 
            $table->string('n_leaching_risk')->nullable(); 
            $table->string('disease_pressure')->nullable(); 
            $table->string('drought_stress_risk')->nullable(); 
            $table->decimal('organic_matter_pct', 5, 2)->nullable(); 

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