<?php

use App\Enums\Feedback\FormRestrictionSubject;
use App\Enums\Feedback\FormRestrictionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mship_feedback_form_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('mship_feedback_forms')->cascadeOnDelete();
            $table->unsignedInteger('restriction_group')->nullable();
            $table->enum('type', array_column(FormRestrictionType::cases(), 'value'));
            $table->enum('subject', array_column(FormRestrictionSubject::cases(), 'value'));
            // For 'qualification': minimum qualification 'vatsim' rank value required (>=)
            // For 'hours': minimum number of hours required (>=)
            $table->unsignedInteger('minimum_value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mship_feedback_form_restrictions');
    }
};
