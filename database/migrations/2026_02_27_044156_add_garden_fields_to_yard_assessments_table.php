<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yard_assessments', function (Blueprint $table) {
            $table->json('garden_types')
                  ->nullable()
                  ->after('completed_at')
                  ->comment("Selected garden types e.g. [\"flowers\",\"vegetables\",\"trees_shrubs\"]");

            $table->string('garden_size', 10)
                  ->nullable()
                  ->after('garden_types')
                  ->comment("Garden size tier: xs | sm | l");

            $table->json('garden_products')
                  ->nullable()
                  ->after('garden_size')
                  ->comment("Computed garden line items: Garden Boost + Garden Revive quarts and pricing");
        });
    }

    public function down(): void
    {
        Schema::table('yard_assessments', function (Blueprint $table) {
            $table->dropColumn(['garden_types', 'garden_size', 'garden_products']);
        });
    }
};