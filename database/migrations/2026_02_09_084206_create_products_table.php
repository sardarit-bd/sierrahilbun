<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->json('benefits')->nullable(); 
            $table->text('ingredients')->nullable();
            $table->text('usage_instructions')->nullable(); 

            $table->integer('coverage_sqft')->nullable();
            $table->decimal('application_rate_oz_per_1k', 8, 2)->nullable();

            $table->decimal('base_price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();

            $table->decimal('rating_avg', 3, 2)->default(0)->index(); 
            $table->unsignedInteger('reviews_count')->default(0);      

            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['category_id', 'is_active']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};