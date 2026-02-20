<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yard_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('session_id', 100)->nullable();
            $table->string('zip_code', 10)->nullable();

            $table->foreign('zip_code')
                  ->references('zip_code')
                  ->on('geo_soil_references')
                  ->nullOnDelete();

            $table->json('selected_services')->nullable();

            $table->unsignedInteger('square_feet')->nullable();
            $table->enum('sqft_source', ['manual', 'calculated', 'confirmed'])->nullable();
            $table->string('address_input', 500)->nullable();
            $table->json('soil_snapshot')->nullable();

            // Step 5: Quiz & Tier
            $table->json('quiz_answers')->nullable();
            $table->enum('resolved_tier', ['bronze', 'silver', 'gold'])->nullable();

            // Step 6: Generated Plan & Pricing
            // DECIMAL not FLOAT — exact arithmetic for financial values
            $table->json('generated_products')->nullable();
            $table->decimal('total_base_price', 10, 2)->nullable();
            $table->decimal('total_addons_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();

            // Flow Control
            $table->tinyInteger('current_step')->unsigned()->default(1);
            $table->enum('status', ['draft', 'completed', 'abandoned'])->default('draft');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('session_id');
            $table->index('zip_code');
            $table->index('status');
            $table->index(['user_id', 'status'], 'idx_user_status');
            $table->index(['status', 'current_step'], 'idx_status_step');
            $table->index(['zip_code', 'resolved_tier'], 'idx_zip_tier');
        });
    }

    public function down(): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Schema::dropIfExists('yard_assessments');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};