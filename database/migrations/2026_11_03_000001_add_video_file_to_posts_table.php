<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correction 13: a video can be uploaded beside the featured image.
 *
 * video_url already existed for a YouTube or Facebook link. This is for a
 * file on the site's own disk, which is a different thing and needs its own
 * column — a path, not a link, and deleted with the article.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('video_file', 500)->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('video_file');
        });
    }
};
