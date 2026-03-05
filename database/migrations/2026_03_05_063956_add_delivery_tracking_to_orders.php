<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_status')
                ->default('pending')
                ->comment('pending, processing, shipped, delivered')
                ->after('status');

            $table->string('tracking_number')
                ->nullable()
                ->after('delivery_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'tracking_number']);
        });
    }
};
