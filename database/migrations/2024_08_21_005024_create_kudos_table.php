<?php

use App\Models\User;
use App\Playback\Playlist;
use App\Playback\SpotifyAccount;
use App\Playback\Track;
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
        Schema::create('kudos', function (Blueprint $table) {
            $table->id();
            $table->string('track_id');
            $table->string('playlist_id')->nullable();
            $table->foreignIdFor(User::class, 'from_user_id')
                ->nullable()
                ->constrained(User::query()->newModelInstance()->getTable());
            $table->foreignIdFor(User::class, 'for_user_id')
                ->nullable()
                ->constrained(User::query()->newModelInstance()->getTable());
            $table->string('for_spotify_account_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('track_id')->references('id')->on('spotify_tracks');
            $table->foreign('playlist_id')->references('id')->on('spotify_playlists');
            $table->foreign('for_spotify_account_id')->references('id')->on('spotify_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kudos');
    }
};
