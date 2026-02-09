<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('restrict');
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            
            // Billing Link
            $table->foreignId('payment_profile_id')->constrained('user_payment_profiles')->onDelete('restrict');
            
            $table->string('status')->comment("'active', 'canceled', 'paused'");
            $table->date('current_period_start');
            $table->date('current_period_end');
            
            // Snapshot for ACID Compliance
            $table->json('soil_data_snapshot')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};