<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mship_role_staff_position', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('role_id');
            $table->string('department');
            $table->string('job_title');
            $table->boolean('is_department_lead')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('mship_role')->cascadeOnDelete();
            $table->unique('role_id');
            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mship_role_staff_position');
    }
};
