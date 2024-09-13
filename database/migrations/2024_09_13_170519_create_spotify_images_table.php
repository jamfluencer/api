<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spotify_images', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('related_type');
            $table->string('related_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_images');
    }
};
