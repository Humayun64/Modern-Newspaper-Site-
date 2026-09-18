<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();        // heading shown above the block
            // which blade partial renders it:
            // main_news | editors_pick | latest_popular | featured_posts | double_columns
            // | posts_slider | posts_grid | express_list | posts_list | single_column
            // | trending | you_may_have_missed
            $table->string('layout', 50);
            // where the posts come from: latest | category | featured | editors_pick | trending | popular
            $table->string('source', 30)->default('latest');
            $table->foreignId('category_id')->nullable()
                  ->constrained('categories')->nullOnDelete();
            $table->unsignedSmallInteger('limit')->default(5);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
