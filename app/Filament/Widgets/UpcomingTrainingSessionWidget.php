<?php

namespace App\Filament\Widgets;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Session;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingTrainingSessionWidget extends Widget
{
    protected static ?int $sort = -4;

    protected static bool $isLazy = false;

    protected string|int|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.upcoming-training-session-widget';

    public ?Session $session = null;

    public ?Carbon $startsAt = null;

    public ?Carbon $endsAt = null;

    public int $remainingSessions = 0;

    public ?ExamBooking $exam = null;

    public ?Carbon $examStartsAt = null;

    public ?Carbon $examEndsAt = null;

    public static function canView(): bool
    {
        return Filament::auth()->check()
            && Filament::auth()->user()?->member !== null;
    }

    public function mount(): void
    {
        $member = Filament::auth()->user()?->member;

        if ($member === null) {
            return;
        }

        $now = now();

        $sessionQuery = Session::query()
            ->where('student_id', $member->id)
            ->whereNull('filed')
            ->whereNull('cancelled_datetime')
            ->where('taken', 1)
            ->where('noShow', 0)
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->whereDate('taken_date', '>', $now->toDateString())
                    ->orWhere(function (Builder $query) use ($now): void {
                        $query
                            ->whereDate('taken_date', $now->toDateString())
                            ->whereTime('taken_to', '>', $now->format('H:i:s'));
                    });
            });

        $this->remainingSessions = max(
            (clone $sessionQuery)->count() - 1,
            0
        );

        $this->session = $sessionQuery
            ->with('mentor')
            ->orderBy('taken_date')
            ->orderBy('taken_from')
            ->first();

        if ($this->session !== null) {
            $date = Carbon::parse($this->session->taken_date)->toDateString();

            $this->startsAt = Carbon::parse(
                "{$date} {$this->session->taken_from}"
            );

            $this->endsAt = Carbon::parse(
                "{$date} {$this->session->taken_to}"
            );
        }

        $this->exam = ExamBooking::query()
            ->with('examiners.primaryExaminer')
            ->where('student_id', $member->id)
            ->where('finished', ExamBooking::NOT_FINISHED_FLAG)
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->whereDate('taken_date', '>', $now->toDateString())
                    ->orWhere(function (Builder $query) use ($now): void {
                        $query
                            ->whereDate('taken_date', $now->toDateString())
                            ->whereTime('taken_to', '>', $now->format('H:i:s'));
                    });
            })
            ->orderBy('taken_date')
            ->orderBy('taken_from')
            ->first();

        if ($this->exam !== null) {
            $date = Carbon::parse($this->exam->taken_date)->toDateString();

            $this->examStartsAt = Carbon::parse(
                "{$date} {$this->exam->taken_from}"
            );

            $this->examEndsAt = Carbon::parse(
                "{$date} {$this->exam->taken_to}"
            );
        }
    }

    public function shouldRender(): bool
    {
        return $this->session !== null || $this->exam !== null;
    }
}
