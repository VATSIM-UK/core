<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_positions', function (Blueprint $table) {
            $table
                ->text('cts_primary_position')
                ->nullable()
                ->after('cts_positions');
        });
    }

    public function down(): void
    {
        Schema::table('training_positions', function (Blueprint $table) {
            $table->dropColumn('cts_primary_position');
        });
    }
};
