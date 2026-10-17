<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correction 20: a "Global coverage" section carrying foreign-media
 * highlights, each with a PDF to open.
 *
 * These are not articles — they have no body, no author and no category, and
 * they are never read on the site itself. Giving them their own small table
 * keeps the posts table honest and means the section cannot accidentally
 * show up in search, feeds or the sitemap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_coverages', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            // "BBC", "Al Jazeera", "The Guardian" — shown as a small label.
            $table->string('outlet', 120)->nullable();
            $table->string('summary', 400)->nullable();

            // Path on the public disk. Nullable so a highlight can be added
            // with a link only, before the PDF is to hand.
            $table->string('pdf_path', 500)->nullable();
            $table->string('cover_path', 500)->nullable();
            $table->string('source_url', 500)->nullable();

            $table->date('published_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_coverages');
    }
};
