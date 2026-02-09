<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->date('scheduled_date');
            $table->string('status')->comment("'pending', 'shipped'");
            
            $table->json('manifest_json');
            $table->string('tracking_number')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['subscription_id', 'scheduled_date']);
            $table->index(['status', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};