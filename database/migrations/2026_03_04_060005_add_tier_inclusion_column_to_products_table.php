<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds tier_inclusion to the products table.
 *
 * Drives TierInclusionMap — determines whether a product is included
 * in a tier's base price or charged as a retail add-on.
 *
 * Values:
 *   bronze → included in Bronze, Silver, and Gold (core products)
 *   silver → included in Silver and Gold only (PatchPro+)
 *   gold   → included in Gold only (Aerate, HeatGuard)
 *   addon  → always charged at retail regardless of tier (default)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('tier_inclusion', ['bronze', 'silver', 'gold', 'addon'])
                  ->default('addon')
                  ->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tier_inclusion');
        });
    }
};