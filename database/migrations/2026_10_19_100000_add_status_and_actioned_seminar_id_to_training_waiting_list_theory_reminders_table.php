<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_waiting_list_theory_reminders', function (Blueprint $table) {
            $table->string('status')->nullable()->after('seminar_id');
            $table->unsignedBigInteger('actioned_seminar_id')->nullable()->after('status');

            $table->foreign('actioned_seminar_id', 'wl_theory_reminders_actioned_seminar_foreign')
                ->references('id')
                ->on('training_seminars')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('training_waiting_list_theory_reminders', function (Blueprint $table) {
            $table->dropForeign('wl_theory_reminders_actioned_seminar_foreign');
            $table->dropColumn(['status', 'actioned_seminar_id']);
        });
    }
};
