<?php

declare(strict_types=1);

use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_places', function (Blueprint $table) {
            $table->string('trainable_type')->nullable()->after('waiting_list_account_id');
            $table->unsignedBigInteger('trainable_id')->nullable()->after('trainable_type');

            $table->index(['trainable_type', 'trainable_id'], 'training_places_trainable_index');
        });

        Schema::table('training_place_offers', function (Blueprint $table) {
            $table->string('trainable_type')->nullable()->after('waiting_list_account_id');
            $table->unsignedBigInteger('trainable_id')->nullable()->after('trainable_type');

            $table->index(['trainable_type', 'trainable_id'], 'training_place_offers_trainable_index');
        });

        DB::table('training_places')
            ->whereNotNull('training_position_id')
            ->update([
                'trainable_type' => TrainingPosition::class,
                'trainable_id' => DB::raw('training_position_id'),
            ]);

        DB::table('training_place_offers')
            ->whereNotNull('training_position_id')
            ->update([
                'trainable_type' => TrainingPosition::class,
                'trainable_id' => DB::raw('training_position_id'),
            ]);

        Schema::table('training_places', function (Blueprint $table) {
            $table->dropForeign(['training_position_id']);
            $table->dropColumn('training_position_id');
        });

        Schema::table('training_place_offers', function (Blueprint $table) {
            $table->dropColumn('training_position_id');
        });
    }

    public function down(): void
    {
        Schema::table('training_places', function (Blueprint $table) {
            $table->unsignedBigInteger('training_position_id')->nullable()->after('waiting_list_account_id');
        });

        Schema::table('training_place_offers', function (Blueprint $table) {
            $table->unsignedBigInteger('training_position_id')->nullable()->after('waiting_list_account_id');
        });

        DB::table('training_places')
            ->where('trainable_type', TrainingPosition::class)
            ->update(['training_position_id' => DB::raw('trainable_id')]);

        DB::table('training_place_offers')
            ->where('trainable_type', TrainingPosition::class)
            ->update(['training_position_id' => DB::raw('trainable_id')]);

        Schema::table('training_places', function (Blueprint $table) {
            $table->foreign('training_position_id')
                ->references('id')
                ->on('training_positions')
                ->onDelete('set null');

            $table->dropIndex('training_places_trainable_index');
            $table->dropColumn(['trainable_type', 'trainable_id']);
        });

        Schema::table('training_place_offers', function (Blueprint $table) {
            $table->dropIndex('training_place_offers_trainable_index');
            $table->dropColumn(['trainable_type', 'trainable_id']);
        });
    }
};
