<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mship_ban_reason', function (Blueprint $table) {
            $table->boolean('is_permanent')->after('period_unit')->default(false);
        });

        DB::table('mship_ban_reason')->updateOrInsert(
            ['name' => 'Permanent Ban'],
            [
                'reason_text' => 'A permanent ban from all VATSIM UK services. This ban does not expire.',
                'period_amount' => 0,
                'period_unit' => 'D',
                'is_permanent' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mship_ban_reason')->where('is_permanent', true)->delete();

        Schema::table('mship_ban_reason', function (Blueprint $table) {
            $table->dropColumn('is_permanent');
        });
    }
};
