<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\MyTraining;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Models\Cts\Member;
use App\Repositories\Cts\SessionRepository;
use Illuminate\Database\Eloquent\Builder;

class MyMentoringHistory extends BaseMentoringHistoryPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected string $view = 'filament.training.pages.my-training.my-mentoring-history';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Mentoring History';

    protected static ?string $slug = 'my-training/mentoring-history';

    protected static ?int $navigationSort = 25;

    public static function canAccess(): bool
    {
        if (! app()->runningUnitTests() && ! auth()->user()?->can('training.beta')) {
            return false;
        }

        return auth()->user()?->can('training.access') ?? false;
    }

    protected function getSessionQuery(): Builder
    {
        $studentId = $this->studentMemberId();

        if ($studentId === null) {
            return (new SessionRepository)->getPastAcceptedSessionsForStudentQuery(0)->whereRaw('0 = 1');
        }

        return (new SessionRepository)->getPastAcceptedSessionsForStudentQuery($studentId);
    }

    protected function getPositionFilterOptions(): array
    {
        $studentId = $this->studentMemberId();

        if ($studentId === null) {
            return [];
        }

        $positions = (new SessionRepository)->getPositionsForStudent($studentId);

        return array_combine($positions, $positions);
    }

    protected function showStudentFilter(): bool
    {
        return false;
    }

    protected function tableEmptyStateHeading(): string
    {
        return 'No mentoring sessions found';
    }

    private function studentMemberId(): ?int
    {
        return Member::query()
            ->where('cid', auth()->id())
            ->value('id');
    }
}
