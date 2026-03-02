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
        Schema::table('yard_assessments', function (Blueprint $table) {
            $table->string('quiz_floor_tier')->nullable()->default('bronze')->after('user_id');
            $table->json('packaging_by_tier')->nullable()->after('quiz_floor_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yard_assessments', function (Blueprint $table) {
            //
        });
    }
};
