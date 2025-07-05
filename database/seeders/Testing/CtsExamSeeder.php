<?php

namespace Database\Seeders\Testing;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use Illuminate\Database\Seeder;

class CtsExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 3 students
        $students = factory(Member::class, 3)->create();

        $webOneCid = 1_000_000_1;
        $webOneId = $webOneCid + 2_000_000;

        if (! Member::find($webOneId)) {
            // add web one as a cts examiner
            factory(Member::class)->create([
                'id' => $webOneId,
                'cid' => $webOneCid,
            ]);
        }

        $students->each(function ($student) {
            ExamBooking::factory()->create([
                'student_id' => $student->id,
            ]);
        });
    }
}
