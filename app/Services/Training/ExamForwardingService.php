<?php

namespace App\Services\Training;

use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ExamForwardingService
{
    /**
     * Forward a member for a practical exam by creating exam setup and booking records.
     *
     * @param  Member  $ctsMember  The CTS member to forward for exam
     * @param  Position  $position  The position/exam to forward the member for
     * @param  int  $setupByUserId  The ID of the user setting up the exam
     * @return array<string, ExamSetup|ExamBooking> Array containing 'setup' and 'examBooking' keys
     *
     * @throws \Exception
     */
    public function forwardForExam(Member $ctsMember, Position $position, int $setupByUserId): array
    {
        // Create the exam setup record
        $setup = ExamSetup::create([
            'rts_id' => $position->rts,
            'student_id' => $ctsMember->id,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => $position->examLevel,
            'setup_by' => $setupByUserId,
            'setup_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'response' => 1,
            'dealt_by' => $setupByUserId,
            'dealt_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        // Create the exam booking record
        $examBooking = ExamBooking::create([
            'rts_id' => $position->rts,
            'student_id' => $ctsMember->id,
            'student_rating' => $ctsMember->account->qualification_atc->vatsim,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => $position->examLevel,
        ]);

        // Link the exam setup to the booking
        $setup->update([
            'bookid' => $examBooking->id,
        ]);

        return [
            'setup' => $setup,
            'examBooking' => $examBooking,
        ];
    }

    /**
     * Forward a member for an OBS exam by creating exam setup and booking records.
     *
     * @param  Member  $ctsMember  The CTS member to forward for exam
     * @param  Model  $position  The OBS position (CtsPosition model)
     * @return array<string, ExamSetup|ExamBooking> Array containing 'setup' and 'examBooking' keys
     *
     * @throws \Exception
     */
    public function forwardForObsExam(Member $ctsMember, Model $position): array
    {
        // Create the exam setup record (rts_id 14 is hard coded for OBS)
        $setup = ExamSetup::create([
            'rts_id' => 14,
            'student_id' => $ctsMember->id,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => 'OBS',
        ]);

        // Create the exam booking record
        $examBooking = ExamBooking::create([
            'rts_id' => 14,
            'student_id' => $ctsMember->id,
            'student_rating' => $ctsMember->account->qualification_atc->vatsim,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => 'OBS',
        ]);

        // Link the exam setup to the booking
        $setup->update([
            'bookid' => $examBooking->id,
        ]);

        return [
            'setup' => $setup,
            'examBooking' => $examBooking,
        ];
    }

    /**
     * Notify about successful exam forwarding
     */
    public function notifySuccess(string $positionCallsign): void
    {
        Notification::make()
            ->title('Exam Setup')
            ->success()
            ->body("Exam setup for {$positionCallsign} has been created.")
            ->send();
    }

    /**
     * Notify about an error
     */
    public function notifyError(string $message): void
    {
        Notification::make()
            ->title('Error')
            ->danger()
            ->body($message)
            ->send();
    }
}
