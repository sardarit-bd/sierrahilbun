<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yard_assessments', function (Blueprint $table) {
            $table->json('recommended_plan_ids')->nullable()->after('resolved_tier');
        });
    }

    public function down(): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Schema::table('yard_assessments', function (Blueprint $table) {
                $table->dropColumn('recommended_plan_ids');
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};