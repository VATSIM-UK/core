<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get type IDs by name
        $typeIds = DB::table('mship_feedback_question_types')
            ->whereIn('name', ['userlookup', 'datetime', 'position_selector', 'radio', 'textarea'])
            ->pluck('id', 'name');

        $atcFormId = DB::table('mship_feedback_forms')
            ->where('slug', 'atc')
            ->value('id');

        if (! $atcFormId) {
            return;
        }

        // Soft delete ALL existing questions on ATC form
        DB::table('mship_feedback_questions')
            ->where('form_id', $atcFormId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        // Create entirely new questions for the multi-step ATC feedback form

        // PAGE 1: Who?
        // CID of the member
        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['userlookup'],
            'form_id' => $atcFormId,
            'slug' => 'usercid',
            'question' => 'What is the CID of the member you are leaving feedback for?',
            'options' => null,
            'required' => true,
            'sequence' => 1,
            'page' => 1,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Date & time
        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['datetime'],
            'form_id' => $atcFormId,
            'slug' => 'datetime',
            'question' => 'When were they controlling?',
            'options' => null,
            'required' => true,
            'sequence' => 2,
            'page' => 1,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Position selector
        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['position_selector'],
            'form_id' => $atcFormId,
            'slug' => 'position_callsign',
            'question' => 'What position were they controlling?',
            'options' => null,
            'required' => true,
            'sequence' => 3,
            'page' => 1,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // PAGE 2: Binary Indicator

        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['radio'],
            'form_id' => $atcFormId,
            'slug' => 'sentiment',
            'question' => 'Is your feedback positive or negative?',
            'options' => json_encode([
                'values' => [
                    'Positive',
                    'Negative',
                ],
            ]),
            'required' => true,
            'sequence' => 1,
            'page' => 2,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // PAGE 3: Feedback

        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['radio'],
            'form_id' => $atcFormId,
            'slug' => 'professional',
            'question' => 'The service they provided was professional and well delivered.',
            'options' => json_encode([
                'values' => [
                    'Strongly disagree',
                    'Disagree',
                    'Neither Agree nor Disagree',
                    'Agree',
                    'Strongly Agree',
                ],
            ]),
            'required' => true,
            'sequence' => 1,
            'page' => 3,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['radio'],
            'form_id' => $atcFormId,
            'slug' => 'competent',
            'question' => 'They were competent.',
            'options' => json_encode([
                'values' => [
                    'Strongly disagree',
                    'Disagree',
                    'Neither Agree nor Disagree',
                    'Agree',
                    'Strongly Agree',
                ],
            ]),
            'required' => true,
            'sequence' => 2,
            'page' => 3,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['radio'],
            'form_id' => $atcFormId,
            'slug' => 'helpful',
            'question' => 'They were helpful and provided all of the information required.',
            'options' => json_encode([
                'values' => [
                    'Strongly disagree',
                    'Disagree',
                    'Neither Agree nor Disagree',
                    'Agree',
                    'Strongly Agree',
                ],
            ]),
            'required' => true,
            'sequence' => 3,
            'page' => 3,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['radio'],
            'form_id' => $atcFormId,
            'slug' => 'enjoyed',
            'question' => 'I enjoyed controlling alongside them.',
            'options' => json_encode([
                'values' => [
                    'N/A',
                    'Strongly disagree',
                    'Disagree',
                    'Neither Agree nor Disagree',
                    'Agree',
                    'Strongly Agree',
                ],
            ]),
            'required' => true,
            'sequence' => 4,
            'page' => 3,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // PAGE 4: Comments

        DB::table('mship_feedback_questions')->insert([
            'type_id' => $typeIds['textarea'],
            'form_id' => $atcFormId,
            'slug' => 'comments',
            'question' => 'Do you have any further comments? You should tell us what the controller did, or did not do, that makes this feedback positive or negative. You should not identify yourself, either by name, CID or callsign. Your comments should be based in fact and should avoid speculation wherever possible.',
            'options' => null,
            'required' => true,
            'sequence' => 1,
            'page' => 4,
            'permanent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $atcFormId = DB::table('mship_feedback_forms')
            ->where('slug', 'atc')
            ->value('id');

        if (! $atcFormId) {
            return;
        }

        DB::table('mship_feedback_questions')
            ->whereIn('slug', [
                'usercid',
                'datetime',
                'position_callsign',
                'sentiment',
                'professional',
                'competent',
                'helpful',
                'enjoyed',
                'comments',
            ])
            ->where('form_id', $atcFormId)
            ->delete();

        DB::table('mship_feedback_questions')
            ->where('form_id', $atcFormId)
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null]);
    }
};
