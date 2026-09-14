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
        Schema::create('trainable_waiting_list', function (Blueprint $table) {
            $table->id();
            $table->string('trainable_type');
            $table->unsignedBigInteger('trainable_id');
            $table->unsignedInteger('waiting_list_id');
            $table->timestamps();

            $table->foreign('waiting_list_id')
                ->references('id')
                ->on('training_waiting_list')
                ->onDelete('cascade');

            $table->unique(['trainable_type', 'trainable_id', 'waiting_list_id'], 'trainable_wl_unique');
            $table->index('waiting_list_id', 'trainable_wl_list_index');
        });

        DB::table('training_position_waiting_list')
            ->orderBy('id')
            ->each(function ($pivot) {
                DB::table('trainable_waiting_list')->insert([
                    'trainable_type' => TrainingPosition::class,
                    'trainable_id' => $pivot->training_position_id,
                    'waiting_list_id' => $pivot->waiting_list_id,
                    'created_at' => $pivot->created_at,
                    'updated_at' => $pivot->updated_at,
                ]);
            });

        Schema::dropIfExists('training_position_waiting_list');
    }

    public function down(): void
    {
        Schema::create('training_position_waiting_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_position_id');
            $table->unsignedInteger('waiting_list_id');
            $table->timestamps();

            $table->foreign('training_position_id')
                ->references('id')
                ->on('training_positions')
                ->onDelete('cascade');

            $table->foreign('waiting_list_id')
                ->references('id')
                ->on('training_waiting_list')
                ->onDelete('cascade');

            $table->unique(['training_position_id', 'waiting_list_id'], 'tp_wl_unique');
        });

        DB::table('trainable_waiting_list')
            ->where('trainable_type', TrainingPosition::class)
            ->orderBy('id')
            ->each(function ($pivot) {
                DB::table('training_position_waiting_list')->insert([
                    'training_position_id' => $pivot->trainable_id,
                    'waiting_list_id' => $pivot->waiting_list_id,
                    'created_at' => $pivot->created_at,
                    'updated_at' => $pivot->updated_at,
                ]);
            });

        Schema::dropIfExists('trainable_waiting_list');
    }
};
