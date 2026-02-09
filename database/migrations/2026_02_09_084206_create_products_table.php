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
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            
            $table->integer('coverage_sqft')->nullable();
            $table->decimal('application_rate_oz_per_1k', 8, 2)->nullable()
                  ->comment('From Python script');
            
            $table->decimal('base_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};