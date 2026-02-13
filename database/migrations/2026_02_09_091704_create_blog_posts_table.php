<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');

            // Core content
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');

            // Media
            $table->string('featured_image_url')->nullable();
            $table->string('featured_image_alt')->nullable();

            // Publishing
            $table->enum('status', ['draft', 'review', 'scheduled', 'published'])->default('draft');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            // Tags (stored as JSON array)
            $table->json('tags')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['is_published', 'published_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};