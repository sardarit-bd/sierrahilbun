<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_payment_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('payment_providers')->onDelete('cascade');
            
            // Generic Tokens
            $table->string('external_customer_id');
            $table->string('external_payment_method_id')->comment('pm_card_visa...');
            $table->boolean('is_default')->default(false);
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_payment_profiles');
    }
};