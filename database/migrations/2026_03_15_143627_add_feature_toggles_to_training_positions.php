<?php

use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_positions', function (Blueprint $table) {
            $table->json('feature_toggles')->nullable()->after('cts_positions');
        });

        TrainingPosition::get()->each(function (TrainingPosition $trainingPosition) {
            $trainingPosition->feature_toggles = [
                'show_recent_controlling' => true,
                'show_solo_endorsement' => true,
            ];
            $trainingPosition->save();
        });
    }

    public function down(): void
    {
        Schema::table('training_positions', function (Blueprint $table) {
            $table->dropColumn('feature_toggles');
        });
    }
};
