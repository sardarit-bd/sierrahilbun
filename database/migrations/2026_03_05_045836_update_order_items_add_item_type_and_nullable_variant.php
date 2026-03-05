<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->change();

            $table->string('item_type')->default('product')->after('order_id');
            $table->unsignedBigInteger('item_id')->nullable()->after('item_type');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable(false)->change();
            $table->dropColumn(['item_type', 'item_id']);
        });
    }
};