<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelescopeEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid');
            $table->uuid('batch_id');
            $table->string('family_hash')->nullable();
            $table->boolean('should_display_on_index')->default(true);
            $table->string('type', 20);
            $table->longText('content');
            $table->dateTime('created_at')->nullable();

            $table->unique('uuid');
            $table->index('batch_id');
            $table->index('family_hash');
            $table->index('created_at');
            $table->index(['type', 'should_display_on_index']);
        });

        Schema::create('telescope_entries_tags', function (Blueprint $table) {
            $table->uuid('entry_uuid');
            $table->string('tag');

            $table->index(['entry_uuid', 'tag']);
            $table->index('tag');

            $table->foreign('entry_uuid')
                  ->references('uuid')
                  ->on('telescope_entries')
                  ->onDelete('cascade');
        });

        Schema::create('telescope_monitoring', function (Blueprint $table) {
            $table->string('tag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIfExists('telescope_entries_tags');
        $this->dropIfExists('telescope_entries');
        $this->dropIfExists('telescope_monitoring');
    }
}
