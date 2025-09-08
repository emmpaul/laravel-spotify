<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('spotify_id')->nullable()->unique()->after('email');
            $table->string('spotify_avatar')->nullable();
            $table->text('spotify_token')->nullable()->after('spotify_id');
            $table->text('spotify_refresh_token')->nullable()->after('spotify_token');
            $table->timestamp('spotify_token_expires_at')->nullable()->after('spotify_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'spotify_id',
                'spotify_avatar',
                'spotify_token',
                'spotify_refresh_token',
                'spotify_token_expires_at',
            ]);
        });
    }
};
