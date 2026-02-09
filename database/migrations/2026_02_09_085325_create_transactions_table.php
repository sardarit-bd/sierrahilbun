<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('payment_providers')->onDelete('restrict');
            
            $table->string('transaction_reference')->unique()->comment('ch_12345 or trx_999');
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('status')->comment("'pending', 'success', 'failed', 'refunded'");
            
            // Debugging
            $table->json('gateway_response_json')->nullable()->comment('Full response from Gateway');
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};