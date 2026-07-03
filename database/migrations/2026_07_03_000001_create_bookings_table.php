<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('mship_account');
            $table->string('type', 20);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->nullableMorphs('bookable');
            $table->unsignedBigInteger('cts_booking_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['position_id', 'starts_at', 'ends_at'], 'idx_bookings_position_overlap');
            $table->index(['member_id', 'starts_at', 'ends_at'], 'idx_bookings_member_overlap');
            $table->index(['type', 'starts_at'], 'idx_bookings_type_starts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
