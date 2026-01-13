<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('cts')->statement('ALTER TABLE `practical_results` MODIFY `date` DATETIME NULL DEFAULT NULL');

        Schema::connection('cts')->table('practical_criteria_assess', function (Blueprint $table) {
            $table->char('previous_result', 1)->nullable()->after('result');
            $table->unsignedInteger('result_updated_by')->nullable()->after('previous_result');
        });

        Schema::connection('cts')->table('practical_results', function (Blueprint $table) {
            $table->char('previous_result', 1)->nullable()->after('result');
            $table->unsignedInteger('result_updated_by')->nullable()->after('previous_result');
            $table->text('result_update_reason')->nullable()->after('result_updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('cts')->table('practical_criteria_assess', function (Blueprint $table) {
            $table->dropColumn('previous_result');
            $table->dropColumn('result_updated_by');
        });

        Schema::connection('cts')->table('practical_results', function (Blueprint $table) {
            $table->dropColumn('previous_result');
            $table->dropColumn('result_updated_by');
            $table->dropColumn('result_update_reason');
        });
    }
};
