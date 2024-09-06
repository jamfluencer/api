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
        Schema::create('spotify_artists_playlist', function (Blueprint $table) {
            $table->string('artist_id');
            $table->string('playlist_id');
            $table->timestamps();

            $table->foreign('artist_id')->references('id')->on('spotify_artists')->onDelete('cascade');
            $table->foreign('playlist_id')->references('id')->on('spotify_playlists')->onDelete('cascade');
        });
        Schema::create('spotify_track_artists', function (Blueprint $table) {
            $table->string('artist_id');
            $table->string('track_id');
            $table->timestamps();

            $table->foreign('artist_id')->references('id')->on('spotify_artists')->onDelete('cascade');
            $table->foreign('track_id')->references('id')->on('spotify_tracks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_pivots');
    }
};
