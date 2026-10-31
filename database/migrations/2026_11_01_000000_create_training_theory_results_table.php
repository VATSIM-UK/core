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
        Schema::create('training_theory_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->string('exam', 10);
            $table->dateTime('exam_datetime');
            $table->char('result', 1);
            $table->timestamps();

            $table->unique(['account_id', 'exam', 'exam_datetime'], 'training_theory_results_dedupe');

            $table->foreign('account_id')
                ->references('id')
                ->on('mship_account')
                ->onDelete('cascade');
        });

        if (! Schema::connection('cts')->hasTable('theory_results') || ! Schema::connection('cts')->hasTable('members')) {
            return;
        }

        // Migrate existing
        DB::connection('cts')
            ->table('theory_results as t')
            ->join('members as m', 'm.id', '=', 't.student_id')
            ->where('t.submitted', 1)
            ->whereNotNull('t.submitted_time')
            ->select(['m.cid', 't.exam', 't.submitted_time', 't.pass'])
            ->orderBy('t.id')
            ->chunk(500, function ($rows): void {
                $now = now();
                $results = [];

                foreach ($rows as $row) {
                    $results[] = [
                        'account_id' => (int) $row->cid,
                        'exam' => (string) $row->exam,
                        'exam_datetime' => (string) $row->submitted_time,
                        'result' => (int) $row->pass === 1 ? 'P' : 'F',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $existingAccounts = DB::table('mship_account')
                    ->whereIn('id', array_unique(array_column($results, 'account_id')))
                    ->pluck('id')
                    ->all();

                $results = array_values(array_filter(
                    $results,
                    fn (array $result) => in_array($result['account_id'], $existingAccounts, true),
                ));

                if ($results !== []) {
                    DB::table('training_theory_results')->insertOrIgnore($results);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_theory_results');
    }
};
