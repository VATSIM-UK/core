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
                ->string('category')
                ->nullable()
                ->after('cts_positions')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('training_positions', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
