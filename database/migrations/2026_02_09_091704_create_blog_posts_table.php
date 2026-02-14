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

            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');

            $table->string('featured_image_url')->nullable();
            $table->string('featured_image_alt')->nullable();

            $table->enum('status', ['draft', 'review', 'scheduled', 'published'])->default('draft');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();


            $table->json('tags')->nullable();

            $table->timestamps();

            $table->index(['is_published', 'published_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};