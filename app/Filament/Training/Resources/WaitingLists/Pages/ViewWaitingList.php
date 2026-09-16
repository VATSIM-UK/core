<?php

namespace App\Filament\Training\Resources\WaitingLists\Pages;

use App\Filament\Training\Resources\WaitingLists\WaitingListResource;
use App\Filament\Training\Resources\WaitingLists\Widgets\IndividualWaitingListOverview;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @property WaitingList $record
 */
class ViewWaitingList extends ViewRecord
{
    protected static string $resource = WaitingListResource::class;

    protected ?array $moodleCourseOptions = null;

    /** @var array<string, array<int, string>> */
    protected array $moodleQuizOptions = [];

    protected function getHeaderWidgets(): array
    {
        return [
            IndividualWaitingListOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_student')
                ->action(function ($data, $action) {
                    $account = Account::find($data['account_id']);
                    $joinDate = Arr::get($data, 'join_date');
                    $createdAt = filled($joinDate) ? Carbon::parse($joinDate) : null;
                    $this->record->addToWaitingList($account, auth()->user(), $createdAt);

                    $action->success();
                })
                ->successNotificationTitle('Student added to waiting list')
                ->after(fn ($livewire) => $livewire->dispatch('refreshWaitingList'))
                ->schema([
                    TextInput::make('account_id')
                        ->label('Account CID')
                        ->rule(fn () => function ($attribute, $value, $fail) {
                            if ($this->record->includesAccount($value)) {
                                $fail('This account is already in this waiting list.');
                            }
                        })
                        ->rule(fn () => function ($attribute, $value, $fail) {
                            if ($this->record->home_members_only) {
                                try {
                                    if (! Account::findOrFail($value)->primary_state->isDivision) {
                                        $fail('The specified member is not a home UK member.');
                                    }
                                } catch (ModelNotFoundException $e) {
                                    Log::debug('Waiting list home member check failed', ['exception' => $e, 'account_id' => $value]);

                                    $fail('The specified member was not found.');
                                }
                            }
                        })
                        ->rule(fn () => function ($attribute, $value, $fail) {
                            try {
                                $account = Account::findOrFail($value);

                                if (! $this->record->accountHasRequiredEndorsement($account)) {
                                    $fail('The specified member does not have the required endorsement to join this waiting list.');
                                }
                            } catch (ModelNotFoundException $e) {
                                Log::debug('Waiting list endorsement check failed', ['exception' => $e, 'account_id' => $value]);

                                $fail('The specified member was not found.');
                            }
                        })
                        ->required(),
                    DatePicker::make('join_date')
                        ->visible(fn () => auth()->user()->can('addAccountsAdmin', $this->record))
                        ->nullable()
                        ->rules(['nullable', 'date', 'after:1970-01-01', 'before_or_equal:today'])
                        ->maxDate(now())
                        ->helperText('This field should only be used to override the date a member joined the waiting list. It is only available to admin-level users.'),
                ])
                ->visible(fn () => auth()->user()->can('addAccounts', $this->record)),

            Action::make('add_flag')
                ->action(function ($data, $action) {
                    $flag = WaitingListFlag::create([
                        'name' => $data['name'],
                        'position_group_id' => $data['position_group_id'] ?? null,
                        'moodle_course_idnumber' => $data['moodle_course_idnumber'] ?? null,
                        'moodle_quiz_id' => $data['moodle_quiz_id'] ?? null,
                        'display_in_table' => $data['display_in_table'] ?? false,
                    ]);

                    $this->record->addFlag($flag);

                    $action->success();
                })
                ->successNotificationTitle('Flag added to waiting list')
                ->schema([
                    TextInput::make('name')->rules(['required', 'min:3',
                        fn () => fn ($attribute, $value, $fail) => $this->record->flags()->where('name', $value)->exists() && $fail('A flag with this name already exists on this waiting list.'),
                    ]),

                    Select::make('position_group_id')->label('Position Group')->options(fn () => PositionGroup::all()->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['name']];
                    }))->hint('If an option is chosen here, this will be an automated flag. This cannot be reversed.')->live()->disabled(fn (Get $get): bool => filled($get('moodle_course_idnumber'))),

                    Select::make('moodle_course_idnumber')
                        ->label('Moodle Course')
                        ->options(fn () => $this->moodleCourseOptions())
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (callable $set) => $set('moodle_quiz_id', null))
                        ->disabled(fn (Get $get): bool => filled($get('position_group_id')))
                        ->visible(fn () => filled(config('services.moodle.database'))),

                    Select::make('moodle_quiz_id')
                        ->label('Moodle Exam')
                        ->options(fn (Get $get): array => $this->moodleQuizOptions($get('moodle_course_idnumber')))
                        ->searchable()
                        ->disabled(fn (Get $get): bool => blank($get('moodle_course_idnumber')) || filled($get('position_group_id')))
                        ->required(fn (Get $get): bool => filled($get('moodle_course_idnumber')))
                        ->placeholder('Select an exam')
                        ->visible(fn () => filled(config('services.moodle.database'))),

                    Toggle::make('display_in_table')
                        ->label('Display in Waiting List Table')
                        ->default(false),
                ])
                ->visible(fn () => auth()->user()->can('addFlags', $this->record)),
            EditAction::make()->label('Edit settings')->visible(fn () => auth()->user()->can('update', $this->record)),
            DeleteAction::make()->label('Delete Waiting List')->requiresConfirmation()->visible(fn () => auth()->user()->can('delete', $this->record)),
        ];
    }

    /**
     * The Moodle courses that can be passed.
     *
     * Only courses with an exam that has a grade to pass configured are listed, because any
     * other course could never make a flag tick.
     */
    protected function moodleCourseOptions(): array
    {
        if (! is_null($this->moodleCourseOptions)) {
            return $this->moodleCourseOptions;
        }

        $moodleDatabase = config('services.moodle.database');

        if (! $moodleDatabase) {
            return $this->moodleCourseOptions = [];
        }

        return $this->moodleCourseOptions = DB::table($moodleDatabase.'.mdl_course as course')
            ->join($moodleDatabase.'.mdl_quiz as quiz', 'quiz.course', '=', 'course.id')
            ->join($moodleDatabase.'.mdl_grade_items as grade_items', function ($join) {
                $join->on('grade_items.iteminstance', '=', 'quiz.id')
                    ->where('grade_items.itemtype', '=', 'mod')
                    ->where('grade_items.itemmodule', '=', 'quiz');
            })
            ->where('grade_items.gradepass', '>', 0)
            ->where('course.idnumber', '!=', '')
            ->orderBy('course.fullname')
            ->get(['course.idnumber', 'course.fullname'])
            ->unique('idnumber')
            ->mapWithKeys(fn ($course) => [$course->idnumber => "{$course->fullname} ({$course->idnumber})"])
            ->all();
    }

    /**
     * The exams within the given Moodle course that can be passed.
     *
     * Only exams with a grade to pass configured are listed, because any other exam could never
     * make a flag tick.
     */
    protected function moodleQuizOptions(?string $courseIdnumber): array
    {
        $moodleDatabase = config('services.moodle.database');

        if (! $moodleDatabase || blank($courseIdnumber)) {
            return [];
        }

        if (array_key_exists($courseIdnumber, $this->moodleQuizOptions)) {
            return $this->moodleQuizOptions[$courseIdnumber];
        }

        return $this->moodleQuizOptions[$courseIdnumber] = DB::table($moodleDatabase.'.mdl_quiz as quiz')
            ->join($moodleDatabase.'.mdl_course as course', 'course.id', '=', 'quiz.course')
            ->join($moodleDatabase.'.mdl_grade_items as grade_items', function ($join) {
                $join->on('grade_items.iteminstance', '=', 'quiz.id')
                    ->where('grade_items.itemtype', '=', 'mod')
                    ->where('grade_items.itemmodule', '=', 'quiz');
            })
            ->where('course.idnumber', $courseIdnumber)
            ->where('grade_items.gradepass', '>', 0)
            ->orderBy('quiz.name')
            ->get(['quiz.id', 'quiz.name', 'grade_items.gradepass'])
            ->mapWithKeys(fn ($quiz) => [$quiz->id => $quiz->name])
            ->all();
    }
}
