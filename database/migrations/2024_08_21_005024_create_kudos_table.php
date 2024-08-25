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
            $table->foreignIdFor(Track::class)
                ->constrained(Track::query()->newModelInstance()->getTable());
            $table->foreignIdFor(Playlist::class)
                ->nullable()
                ->constrained(Playlist::query()->newModelInstance()->getTable());
            $table->foreignIdFor(User::class, 'from_user_id')
                ->nullable()
                ->constrained(User::query()->newModelInstance()->getTable());
            $table->foreignIdFor(User::class, 'for_user_id')
                ->nullable()
                ->constrained(User::query()->newModelInstance()->getTable());
            $table->foreignIdFor(SpotifyAccount::class, 'for_spotify_account_id')
                ->constrained(SpotifyAccount::query()->newModelInstance()->getTable());
            $table->timestamps();
            $table->softDeletes();
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
