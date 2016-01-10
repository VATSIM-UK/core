<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BanReasonImprovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_note_type', function(Blueprint $table) {
            $table->string('short_code', 20)->nullable()->after("name");
            $table->boolean("is_default")->default(0)->after("is_system");
        });

        DB::table("mship_note_type")->insert([
            ["name" => "Discipline", "short_code" => "discipline", "is_available" => 1, "is_system" => 1, "colour_code" => "danger", "created_at" => \Carbon\Carbon::now(), "updated_at" => \Carbon\Carbon::now()],
        ]);

        DB::table("mship_note_type")->where("name", "=", "System Generated")->update(["short_code" => "system", "is_default" => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table("mship_note_type")->where("short_code", "=", "discipline")->delete();

        Schema::table('mship_note_type', function(Blueprint $table) {
            $table->dropColumn("short_code");
        });
    }
}
