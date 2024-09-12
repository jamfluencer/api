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
        Schema::create('spotify_album_tracks', function (Blueprint $table) {
            $table->string('album_id');
            $table->string('track_id');
            $table->timestamps();

            $table->foreign('album_id')->references('id')->on('spotify_albums')->onDelete('cascade');
            $table->foreign('track_id')->references('id')->on('spotify_tracks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_album_tracks');
    }
};
