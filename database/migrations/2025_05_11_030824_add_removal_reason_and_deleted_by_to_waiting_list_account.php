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
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->unsignedInteger('removed_by')->nullable()->after('deleted_at');
            $table->string('removal_type')->nullable()->after('removed_by');
        });
        //Insert new note type
        DB::table('mship_note_type')->insert([
            'name' => 'Training',
            'short_code' => 'training',
            'is_available' => true,
            'is_system' => true,
            'is_default' => false,
            'colour_code' => 'info',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->dropColumn('removed_by');
            $table->dropColumn('removal_type');
        });
        //Removes note type
        DB::table('mship_note_type')->where('short_code', 'training')->delete();
    }
};
