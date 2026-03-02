<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_product', function (Blueprint $table) {
            $table->foreignId('feature_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->string('product_sku', 100)
                  ->references('sku')
                  ->on('products')
                  ->onDelete('cascade');

            $table->primary(['feature_id', 'product_sku']);

            $table->index('product_sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_product');
    }
};