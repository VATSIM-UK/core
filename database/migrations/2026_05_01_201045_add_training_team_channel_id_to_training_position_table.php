<?php

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
        Schema::table('training_positions', function (Blueprint $table) {
            $table->string('training_team_discord_channel_id')->nullable()->after('category');
        });

        $categoryChannelMap = [
            'OBS to S1 Training' => '705819268067098736',
            'S2 Training' => '705818708018200646',
            'S3 Training' => '705818720471089262',
            'C1 Training' => '827491230078337034',
            'Heathrow GMC' => '827491193214468106',
            'Heathrow AIR' => '827491193214468106',
            'Heathrow APC' => '827491193214468106',
        ];

        foreach ($categoryChannelMap as $category => $channelId) {
            DB::table('training_positions')
                ->where('category', $category)
                ->update(['training_team_discord_channel_id' => $channelId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_positions', function (Blueprint $table) {
            $table->dropColumn('training_team_discord_channel_id');
        });
    }
};
