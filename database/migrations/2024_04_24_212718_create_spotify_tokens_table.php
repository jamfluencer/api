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
        Schema::create('spotify_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('token');
            $table->string('type')->default('Bearer');
            $table->string('scope')->default('');
            $table->integer('expiry');
            $table->string('refresh');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_tokens');
    }
};
