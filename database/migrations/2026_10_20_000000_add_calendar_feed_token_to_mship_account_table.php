<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->string('calendar_feed_token', 48)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropUnique(['calendar_feed_token']);
            $table->dropColumn('calendar_feed_token');
        });
    }
};
