<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUuidToFailedJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs_failed', function (Blueprint $table) {
            $table->string('uuid')->after('id')->nullable()->unique();
        });

        // Generate uuid for existing rows
        DB::table('jobs_failed')->whereNull('uuid')->cursor()->each(function ($job) {
            DB::table('jobs_failed')
                ->where('id', $job->id)
                ->update(['uuid' => (string) Illuminate\Support\Str::uuid()]);
        });
    }
}
