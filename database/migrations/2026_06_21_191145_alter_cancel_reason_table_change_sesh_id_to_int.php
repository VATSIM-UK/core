<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('cts')->table('cancel_reason', function (Blueprint $table) {
            $table->integer('sesh_id')->unsigned()->change();
        });
    }

    public function down(): void
    {
        Schema::connection('cts')->table('cancel_reason', function (Blueprint $table) {
            $table->smallInteger('sesh_id')->unsigned()->change();
        });
    }
};
