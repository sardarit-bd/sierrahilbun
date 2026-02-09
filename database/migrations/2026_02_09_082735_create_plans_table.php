<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment("'Keep & Protect', 'Lawn & Paws'");
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            $table->decimal('base_price_yearly', 10, 2);
            $table->decimal('current_price_yearly', 10, 2)->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->string('target_audience')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};