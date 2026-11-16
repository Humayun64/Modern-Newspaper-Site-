<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The search term a writer wants an article to be found by.
 *
 * It is not shown to readers and never goes into the page — it exists so
 * the SEO panel has something to check the headline, the opening and the
 * meta description against. Without it the panel can only measure lengths.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'focus_keyphrase')) {
                $table->string('focus_keyphrase', 191)->nullable()->after('meta_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'focus_keyphrase')) {
                $table->dropColumn('focus_keyphrase');
            }
        });
    }
};
