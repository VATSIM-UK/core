<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->bigInteger('ukcp_position_id')
                ->unsigned()
                ->nullable()
                ->unique()
                ->after('id');

            $table->json('top_down')
                ->nullable()
                ->after('ukcp_position_id');

            $table->softDeletes()
                ->after('updated_at');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('sub_station');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->boolean('sub_station')
                ->default(false)
                ->after('type');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique(['ukcp_position_id']);
            $table->dropColumn('ukcp_position_id');
            $table->dropColumn('top_down');
            $table->dropSoftDeletes();
        });
    }
};
