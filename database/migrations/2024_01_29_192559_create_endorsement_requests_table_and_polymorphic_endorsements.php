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
        Schema::create('endorsement_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->morphs('endorsable');
            $table->timestamp('endorsable_expires_at')->nullable();
            $table->unsignedInteger('requested_by');
            $table->timestamp('actioned_at')->nullable();
            $table->string('actioned_type')->nullable();
            $table->unsignedInteger('actioned_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('mship_account_endorsement', function (Blueprint $table) {
            $table->dropColumn('position_group_id');
            $table->morphs('endorsable');
            $table->unsignedInteger('endorsement_request_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endorsement_requests');

        Schema::table('mship_account_endorsement', function (Blueprint $table) {
            $table->dropMorphs('endorsable');
            $table->unsignedInteger('position_group_id');
            $table->dropColumn('endorsement_request_id');
        });
    }
};
