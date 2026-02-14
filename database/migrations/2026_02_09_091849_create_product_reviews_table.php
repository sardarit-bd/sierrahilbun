<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->unsignedTinyInteger('rating'); 
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            
            $table->json('images_json')->nullable();

            $table->unsignedInteger('helpful_count')->default(0); 
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(false);
            
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};