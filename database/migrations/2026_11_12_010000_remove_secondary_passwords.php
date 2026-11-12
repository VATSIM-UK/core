<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn(['password', 'password_set_at', 'password_expires_at']);
        });

        // Roles that previously mandated a secondary password are now mandatorily 2FA.
        DB::table('mship_role')
            ->where('password_mandatory', true)
            ->update(['two_factor_mandatory' => true]);

        Schema::table('mship_role', function (Blueprint $table) {
            $table->dropColumn(['password_mandatory', 'password_lifetime']);
        });

        Schema::dropIfExists('password_resets');

        DB::table('mship_permission')
            ->where('name', 'account.remove-password.*')
            ->update(['name' => 'account.reset-two-factor.*']);
    }

    public function down(): void
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->string('password', 60)->nullable();
            $table->timestamp('password_set_at')->nullable();
            $table->timestamp('password_expires_at')->nullable();
        });

        Schema::table('mship_role', function (Blueprint $table) {
            $table->boolean('password_mandatory')->default(false);
            $table->unsignedInteger('password_lifetime')->default(180);
        });

        DB::table('mship_permission')
            ->where('name', 'account.reset-two-factor.*')
            ->update(['name' => 'account.remove-password.*']);
    }
};
