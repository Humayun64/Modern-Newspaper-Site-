<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Correction 13, done properly.
 *
 * The first attempt measured the day from publish time. That is wrong for
 * this site: every imported article is weeks old, so marking one "breaking"
 * did nothing — it was already past the cut-off before the editor clicked.
 *
 * What the client means is a day from the moment it was MARKED breaking.
 * That needs its own timestamp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('breaking_since')->nullable()->after('is_breaking');
            $table->index(['is_breaking', 'breaking_since']);
        });

        // Anything already flagged starts its day now, rather than vanishing
        // the moment this runs.
        DB::table('posts')
            ->where('is_breaking', true)
            ->whereNull('breaking_since')
            ->update(['breaking_since' => now()]);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['is_breaking', 'breaking_since']);
            $table->dropColumn('breaking_since');
        });
    }
};
