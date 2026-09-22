<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_managers', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id');
            $table->unsignedInteger('account_id');
            $table->primary(['event_id', 'account_id']);
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('mship_account')->cascadeOnDelete();
        });

        // preserve the single manager each event may already have.
        DB::table('events')
            ->whereNotNull('manager_id')
            ->get(['id', 'manager_id'])
            ->each(function (object $event): void {
                DB::table('event_managers')->insertOrIgnore([
                    'event_id' => $event->id,
                    'account_id' => $event->manager_id,
                ]);
            });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('manager_id')->nullable()->after('published_by');
            $table->foreign('manager_id')->references('id')->on('mship_account')->nullOnDelete();
        });

        // collapse back to the first manager assigned to each event.
        DB::table('event_managers')
            ->orderBy('event_id')
            ->get(['event_id', 'account_id'])
            ->groupBy('event_id')
            ->each(function ($managers, $eventId): void {
                DB::table('events')
                    ->where('id', $eventId)
                    ->update(['manager_id' => $managers->first()->account_id]);
            });

        Schema::dropIfExists('event_managers');
    }
};
