<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // --- content ---
            $table->string('title', 500);
            $table->string('slug', 191)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('image_caption', 500)->nullable();
            $table->string('image_credit')->nullable();

            // --- relations ---
            // category_id is the PRIMARY category (used in the URL and the main badge).
            // Extra categories live in the post_category pivot.
            $table->foreignId('category_id')->nullable()
                  ->constrained('categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            // --- post type ---
            // standard | video | gallery | podcast | live
            $table->string('type', 20)->default('standard');
            $table->string('video_url')->nullable();
            $table->string('audio_url')->nullable();

            // --- publishing ---
            // draft | pending | published | scheduled
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();

            // --- homepage placement flags ---
            $table->boolean('is_breaking')->default(false);
            $table->boolean('is_featured')->default(false);      // Main News slider
            $table->boolean('is_editors_pick')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->unsignedTinyInteger('trending_position')->nullable(); // 1..5
            $table->boolean('is_sponsored')->default(false);     // paid / guest post label

            // --- stats ---
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedSmallInteger('reading_time')->nullable(); // minutes

            // --- seo ---
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();

            // --- wordpress import ---
            $table->unsignedBigInteger('wp_id')->nullable()->unique();
            $table->string('legacy_url', 500)->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Indexes that actually matter on a news site.
            $table->index(['status', 'published_at']);                 // every public listing
            $table->index(['category_id', 'status', 'published_at']);  // category pages
            $table->index(['is_breaking', 'status', 'published_at']);  // ticker
            $table->index(['is_featured', 'status', 'published_at']);  // slider
            $table->index(['status', 'views']);                        // popular tab
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
