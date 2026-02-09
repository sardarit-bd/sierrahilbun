<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('product_sku')->comment('What actually ships');
            $table->integer('quantity')->default(1);
            $table->integer('shipment_month')->comment('Month 1, Month 3, etc.');
            
            $table->index('product_sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_deliverables');
    }
};