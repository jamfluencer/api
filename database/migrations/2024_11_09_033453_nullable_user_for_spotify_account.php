<?php

use App\Models\User;
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
        Schema::table('spotify_accounts', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spotify_accounts', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->nullable(false)->change();
        });
    }
};
