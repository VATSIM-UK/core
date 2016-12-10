<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunityGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_group', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name', 30);
            $table->smallInteger('tier')->nullable();
            $table->longText('coordinate_boundaries');
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('community_group')->insert([
            ['name' => 'UK Community', 'tier' => 1, 'default' => true],
            ['name' => 'Scotland', 'tier' => 2, 'default' => false],
            ['name' => 'Northern Ireland', 'tier' => 2, 'default' => false],
            ['name' => 'Northern', 'tier' => 2, 'default' => false],
            ['name' => 'Midlands', 'tier' => 2, 'default' => false],
            ['name' => 'Wales', 'tier' => 2, 'default' => false],
            ['name' => 'East Anglia', 'tier' => 2, 'default' => false],
            ['name' => 'South West', 'tier' => 2, 'default' => false],
            ['name' => 'South East', 'tier' => 2, 'default' => false],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_group');
    }
}
