<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id');
            $table->string('question_key')->comment("'pet_traffic'");
            $table->string('answer_value')->comment("'high'");
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('session_id')
                  ->references('id')
                  ->on('onboarding_sessions')
                  ->onDelete('cascade');
            
            $table->index(['session_id', 'question_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_quiz_answers');
    }
};