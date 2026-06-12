<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->boolean('is_vt')->default(false)->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->dropColumn('is_vt');
        });
    }
};
