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
        Schema::table('spotify_tokens', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->after('expiry');
            $table->dropColumn('expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spotify_tokens', function (Blueprint $table) {
            $table->integer('expiry')->after('expires_at');
            $table->dropColumn('expires_at');
        });
    }
};
