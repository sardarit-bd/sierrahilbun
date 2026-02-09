<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')
                  ->comment('Nullable (Guest -> User)');
            $table->uuid('onboarding_session_id')->nullable();
            
            // Location
            $table->string('zip_code', 10);
            $table->string('address_line_1');
            $table->string('city');
            $table->string('state');
            
            // The Asset Data
            $table->integer('measured_sqft');
            $table->string('sqft_source')->comment("'satellite', 'manual'");
            $table->json('lot_polygon_coordinates')->nullable()
                  ->comment('SAVE THIS. Prevents re-calling Satellite API');
            $table->string('satellite_snapshot_url')->nullable();
            
            $table->timestamps();
            
            $table->foreign('onboarding_session_id')
                  ->references('id')
                  ->on('onboarding_sessions')
                  ->onDelete('set null');
            
            $table->foreign('zip_code')
                  ->references('zip_code')
                  ->on('geo_soil_references')
                  ->onDelete('restrict');
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};