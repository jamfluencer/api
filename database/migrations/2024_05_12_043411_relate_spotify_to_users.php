<?php

use App\Models\User;
use App\Playback\SpotifyAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('spotify_tokens')->truncate();
        Schema::create('spotify_accounts', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('country')->nullable();
            $table->string('display_name');
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });

        Schema::table('spotify_tokens', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->foreignIdFor(SpotifyAccount::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('spotify_tokens')->truncate();
        Schema::table('spotify_tokens', function (Blueprint $table) {
            $table->dropColumn('spotify_account_id');
            $table->foreignIdFor(User::class);
        });
        Schema::drop('spotify_accounts');
    }
};
