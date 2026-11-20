<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_seminars', function (Blueprint $table) {
            $table->string('training_group')
                ->default('OBS to S1 Training')
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('training_seminars', function (Blueprint $table) {
            $table->dropColumn('training_group');
        });
    }
};
