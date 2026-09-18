<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->unique()->after('name');
            $table->string('name_bn')->nullable()->after('slug');
            $table->string('role', 20)->default('author')->after('password'); // admin | editor | author
            $table->string('designation')->nullable();   // স্টাফ রিপোর্টার, বিশেষ প্রতিনিধি
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('youtube')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'name_bn', 'role', 'designation', 'bio', 'photo',
                'facebook', 'twitter', 'youtube', 'is_active',
            ]);
        });
    }
};
