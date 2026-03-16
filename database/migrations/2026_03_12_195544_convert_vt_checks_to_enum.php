<?php

use App\Enums\VTCheckStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // widen to a temporary enum that accepts the legacy integer values
        Schema::table('vt_application', function (Blueprint $table) {
            $enumValues = ['0', '1', ...array_column(VTCheckStatus::cases(), 'value')];
            $table->enum('check_outcome_90_day', $enumValues)->nullable()->change();
            $table->enum('check_outcome_50_hours', $enumValues)->nullable()->change();
        });

        DB::statement("
            UPDATE vt_application
            SET check_outcome_90_day = CASE check_outcome_90_day
                    WHEN '1' THEN 'passed'
                    WHEN '0' THEN 'failed'
                    ELSE 'pending'
                END,
                check_outcome_50_hours = CASE check_outcome_50_hours
                    WHEN '1' THEN 'passed'
                    WHEN '0' THEN 'failed'
                    ELSE 'pending'
                END
        ");

        Schema::table('vt_application', function (Blueprint $table) {
            $enumValues = array_column(VTCheckStatus::cases(), 'value');
            $table->enum('check_outcome_90_day', $enumValues)->default(VTCheckStatus::Pending->value)->change();
            $table->enum('check_outcome_50_hours', $enumValues)->default(VTCheckStatus::Pending->value)->change();
        });
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE vt_application
            MODIFY COLUMN check_outcome_90_day ENUM('pending','failed','passed','not_required') NULL DEFAULT NULL,
            MODIFY COLUMN check_outcome_50_hours ENUM('pending','failed','passed','not_required') NULL DEFAULT NULL
        ");

        DB::statement("
            UPDATE vt_application
            SET check_outcome_90_day = CASE check_outcome_90_day
                    WHEN 'passed' THEN 'passed'
                    WHEN 'failed' THEN 'failed'
                    ELSE NULL
                END,
                check_outcome_50_hours = CASE check_outcome_50_hours
                    WHEN 'passed' THEN 'passed'
                    WHEN 'failed' THEN 'failed'
                    ELSE NULL
                END
        ");

        DB::statement('
            ALTER TABLE vt_application
            MODIFY COLUMN check_outcome_90_day VARCHAR(12) NULL DEFAULT NULL,
            MODIFY COLUMN check_outcome_50_hours VARCHAR(12) NULL DEFAULT NULL
        ');

        DB::statement("
            UPDATE vt_application
            SET check_outcome_90_day = CASE check_outcome_90_day
                    WHEN 'passed' THEN '1'
                    WHEN 'failed' THEN '0'
                    ELSE NULL
                END,
                check_outcome_50_hours = CASE check_outcome_50_hours
                    WHEN 'passed' THEN '1'
                    WHEN 'failed' THEN '0'
                    ELSE NULL
                END
        ");

        DB::statement('
            ALTER TABLE vt_application
            MODIFY COLUMN check_outcome_90_day TINYINT(1) NULL DEFAULT NULL,
            MODIFY COLUMN check_outcome_50_hours TINYINT(1) NULL DEFAULT NULL
        ');
    }
};
