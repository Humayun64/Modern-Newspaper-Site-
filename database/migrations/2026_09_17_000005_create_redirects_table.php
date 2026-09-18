<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            // Stored without the leading slash and without the domain, so a
            // redirect keeps working if the site moves or changes protocol.
            $table->string('from_path', 500)->unique();
            $table->string('to_path', 500);

            $table->unsignedSmallInteger('status')->default(301);
            $table->string('source', 20)->default('manual');   // manual | import
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
