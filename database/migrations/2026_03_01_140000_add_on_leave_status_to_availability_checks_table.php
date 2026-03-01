<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE availability_checks MODIFY status ENUM('passed', 'failed', 'on_leave') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE availability_checks MODIFY status ENUM('passed', 'failed') NOT NULL");
    }
};
