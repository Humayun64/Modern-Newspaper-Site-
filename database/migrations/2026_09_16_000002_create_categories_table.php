<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // ব্রেকিং
            $table->string('name_en')->nullable();        // Breaking
            $table->string('slug', 191)->unique();        // breaking
            $table->foreignId('parent_id')->nullable()
                  ->constrained('categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();      // #d32f2f for the category badge
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->unsignedBigInteger('wp_term_id')->nullable()->index(); // for the WP import
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
