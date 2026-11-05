<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mship_role_helpdesk_staff_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('helpdesk_department_id');
            $table->unsignedInteger('helpdesk_team_id');
            $table->unsignedInteger('helpdesk_role_id');
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('mship_role')->cascadeOnDelete();
            $table->unique('role_id');
            $table->index('priority');
        });

        Schema::create('mship_role_helpdesk_staff_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')
                ->constrained('mship_role_helpdesk_staff_setting')
                ->cascadeOnDelete();
            $table->unsignedInteger('helpdesk_department_id');
            $table->unsignedInteger('helpdesk_role_id');
            $table->timestamps();

            $table->unique(['setting_id', 'helpdesk_department_id'], 'helpdesk_staff_access_department_unique');
        });

        Schema::create('mship_helpdesk_staff_account', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('helpdesk_staff_id');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('mship_account')->cascadeOnDelete();
            $table->unique('account_id');
            $table->unique('helpdesk_staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mship_helpdesk_staff_account');
        Schema::dropIfExists('mship_role_helpdesk_staff_access');
        Schema::dropIfExists('mship_role_helpdesk_staff_setting');
    }
};
