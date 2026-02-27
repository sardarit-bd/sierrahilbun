<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();

            // Author info 
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('author_name');
            $table->string('author_title')->nullable();
            $table->string('author_avatar_url')->nullable();

            // Content
            $table->text('body');
            $table->tinyInteger('rating')->unsigned()->nullable();

            // Moderation
            $table->string('status')->default('pending'); 
            $table->boolean('is_featured')->default(false);
            $table->timestamp('approved_at')->nullable();

            // Origin tracking
            $table->string('source')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index(['status', 'is_featured']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};