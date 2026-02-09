<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_deliverables', function (Blueprint $table) {
            $table->foreign('product_sku')
                  ->references('sku')
                  ->on('products')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('plan_deliverables', function (Blueprint $table) {
            $table->dropForeign(['product_sku']);
        });
    }
};