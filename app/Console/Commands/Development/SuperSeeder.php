<?php

namespace App\Console\Commands\Development;

use App\Models\Airport;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupCondition;
use App\Models\Mship\Account;
use App\Models\Mship\Ban\Reason as BanReason;
use App\Models\Mship\Feedback\Answer;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use App\Models\Mship\Feedback\Question\Type as QuestionType;
use App\Models\Mship\Note\Type as NoteType;
use App\Models\Mship\Qualification;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Models\Training\WaitingList;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Models\VisitTransfer\Reference;
use Database\Seeders\WaitingListStressSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SuperSeeder extends Command
{
    protected $signature = 'db:super-seed {--tables=* : Specific tables to seed}';

    protected $description = 'Seeds all tables with realistic-looking data for development purposes.';

    private array $accounts = [];

    private array $positions = [];

    private array $positionGroups = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->isLocalEnvironment()) {
            return $this->exitWithError('This command can only be executed in the local environment for security reasons.');
        }

        // Fake notifications to prevent email/Discord errors during seeding
        Notification::fake();

        $this->info('Starting super seeder...');
        $tables = $this->option('tables');

        if (empty($tables)) {
            // Seed everything
            $this->seedAll();
        } else {
            // Seed specific tables
            foreach ($tables as $table) {
                $method = 'seed'.str_replace('_', '', ucwords($table, '_'));
                if (method_exists($this, $method)) {
                    $this->$method();
                } else {
                    $this->warn("Seeder method {$method} does not exist for table {$table}");
                }
            }
        }

        $this->info('Super seeder completed!');

        return 0;
    }

    /**
     * Check if running in local environment
     */
    private function isLocalEnvironment(): bool
    {
        return app()->environment('local');
    }

    /**
     * Exit command with error message
     */
    private function exitWithError(string $message): int
    {
        $this->error($message);

        return 1;
    }

    /**
     * Seed all tables
     */
    private function seedAll(): void
    {
        // Seed core dependencies first
        $this->info('Seeding core dependencies...');
        $this->seedQualifications();
        $this->seedBanReasons();
        $this->seedNoteTypes();

        // Seed accounts and related data
        $this->info('Seeding accounts...');
        $this->seedAccounts();
        $this->seedAccountEmails();
        $this->seedAccountStates();
        $this->seedAccountQualifications();
        $this->seedAccountRoles();
        $this->seedAccountBans();
        $this->seedAccountNotes();

        // Seed positions and groups
        $this->info('Seeding positions...');
        $this->seedPositions();
        $this->seedPositionGroups();
        $this->seedPositionGroupPositions();
        $this->seedPositionGroupConditions();
        $this->seedAirportPositions();

        // Seed endorsements
        $this->info('Seeding endorsements...');
        $this->seedAccountEndorsements();
        $this->seedEndorsementRequests();

        // Seed feedback system
        $this->info('Seeding feedback system...');
        $this->seedFeedbackForms();
        $this->seedFeedbackQuestionTypes();
        $this->seedFeedbackQuestions();
        $this->seedFeedback();
        $this->seedFeedbackAnswers();

        // Seed roster
        $this->info('Seeding roster...');
        $this->seedRoster();
        $this->seedRosterHistory();

        // Seed waiting lists (using existing seeder)
        $this->info('Seeding waiting lists...');
        $this->call(WaitingListStressSeeder::class);
        $this->seedWaitingListRetentionChecks();

        // Seed visit/transfer
        $this->info('Seeding visit/transfer...');
        $this->seedVtFacilities();
        $this->seedVtApplications();
        $this->seedVtReferences();

        // Seed CTS
        $this->info('Seeding CTS...');
        $this->seedCts();

        // Seed Discord
        $this->info('Seeding Discord...');
        $this->seedDiscordRoleRules();
    }

    private function seedQualifications(): void
    {
        if (Qualification::count() > 0) {
            $this->line('Qualifications already seeded, skipping...');

            return;
        }

        Qualification::factory()->atc()->create(['id' => 1, 'code' => 'S1', 'vatsim' => 2, 'name_small' => 'OBS', 'name_long' => 'Observer']);
        Qualification::factory()->atc()->create(['id' => 2, 'code' => 'S2', 'vatsim' => 3, 'name_small' => 'S1', 'name_long' => 'Tower']);
        Qualification::factory()->atc()->create(['id' => 3, 'code' => 'S3', 'vatsim' => 4, 'name_small' => 'S2', 'name_long' => 'Approach']);
        Qualification::factory()->atc()->create(['id' => 4, 'code' => 'C1', 'vatsim' => 5, 'name_small' => 'S3', 'name_long' => 'Enroute']);
        Qualification::factory()->pilot()->create(['id' => 5, 'code' => 'P0', 'vatsim' => 0, 'name_small' => 'P0', 'name_long' => 'New Member']);
        $this->line('Qualifications seeded.');
    }

    private function seedBanReasons(): void
    {
        if (BanReason::count() > 0) {
            $this->line('Ban reasons already exist, skipping...');

            return;
        }
        BanReason::factory()->count(3)->create();
        $this->line('Ban reasons seeded.');
    }

    private function seedNoteTypes(): void
    {
        if (NoteType::count() > 0) {
            $this->line('Note types already exist, skipping...');

            return;
        }
        NoteType::create(['name' => 'General']);
        NoteType::create(['name' => 'Warning']);
        $this->line('Note types seeded.');
    }

    private function seedAccounts(): void
    {
        $count = Account::count();
        if ($count >= 50) {
            $this->line("Accounts already seeded ({$count} exist), skipping...");
            $this->accounts = Account::limit(50)->get()->all();

            return;
        }

        $this->accounts = Account::factory()->count(50)->create()->all();
        $this->line('50 accounts created.');
    }

    private function seedAccountEmails(): void
    {
        foreach ($this->accounts as $account) {
            if ($account->secondaryEmails()->count() === 0) {
                $account->addSecondaryEmail(fake()->unique()->safeEmail(), true);
            }
        }
        $this->line('Account emails seeded.');
    }

    private function seedAccountStates(): void
    {
        foreach ($this->accounts as $account) {
            if ($account->states()->count() === 0) {
                $stateCodes = ['DIVISION', 'VISITING', 'REGION', 'INTERNATIONAL'];
                $state = \App\Models\Mship\State::where('code', $stateCodes[array_rand($stateCodes)])->first();
                if ($state) {
                    $account->addState($state, 'EUR', 'GBR');
                }
            }
        }
        $this->line('Account states seeded.');
    }

    private function seedAccountQualifications(): void
    {
        $qualifications = Qualification::all();
        foreach ($this->accounts as $account) {
            if ($account->qualifications()->count() === 0 && $qualifications->count() > 0) {
                $account->qualifications()->attach($qualifications->random(rand(1, min(3, $qualifications->count()))));
            }
        }
        $this->line('Account qualifications seeded.');
    }

    private function seedAccountRoles(): void
    {
        $roles = \Spatie\Permission\Models\Role::all();
        foreach (array_slice($this->accounts, 0, 10) as $account) {
            if ($account->roles()->count() === 0 && $roles->count() > 0) {
                $account->assignRole($roles->random());
            }
        }
        $this->line('Account roles seeded.');
    }

    private function seedAccountBans(): void
    {
        $reasons = BanReason::all();
        if ($reasons->count() === 0) {
            $this->line('No ban reasons available, skipping account bans...');

            return;
        }

        foreach (array_slice($this->accounts, 0, 5) as $account) {
            if ($account->bans()->count() === 0) {
                $account->addBan(
                    $reasons->random(),
                    'Sample ban for testing purposes',
                    'Banned during seeding',
                    $this->accounts[0] ?? null
                );
            }
        }
        $this->line('Account bans seeded.');
    }

    private function seedAccountNotes(): void
    {
        $noteTypes = NoteType::all();
        if ($noteTypes->count() === 0) {
            $this->line('No note types available, skipping account notes...');

            return;
        }

        foreach (array_slice($this->accounts, 0, 20) as $account) {
            if ($account->notes()->count() === 0) {
                $account->addNote(
                    $noteTypes->random(),
                    'Sample note for testing purposes.',
                    $this->accounts[0] ?? null
                );
            }
        }
        $this->line('Account notes seeded.');
    }

    private function seedPositions(): void
    {
        $count = Position::count();
        if ($count >= 20) {
            $this->line("Positions already seeded ({$count} exist), skipping...");
            $this->positions = Position::limit(20)->get()->all();

            return;
        }

        $this->positions = Position::factory()->count(20)->create()->all();
        $this->line('20 positions created.');
    }

    private function seedPositionGroups(): void
    {
        $count = PositionGroup::count();
        if ($count >= 5) {
            $this->line("Position groups already seeded ({$count} exist), skipping...");
            $this->positionGroups = PositionGroup::limit(5)->get()->all();

            return;
        }

        $this->positionGroups = PositionGroup::factory()->count(5)->create()->all();
        $this->line('5 position groups created.');
    }

    private function seedPositionGroupPositions(): void
    {
        if (empty($this->positions) || empty($this->positionGroups)) {
            $this->line('No positions or groups to associate, skipping...');

            return;
        }

        foreach ($this->positionGroups as $group) {
            $positionsToAttach = array_slice($this->positions, 0, rand(2, 5));
            $positionIds = array_map(fn ($pos) => $pos->id, $positionsToAttach);
            $group->positions()->syncWithoutDetaching($positionIds);
        }
        $this->line('Position group positions seeded.');
    }

    private function seedPositionGroupConditions(): void
    {
        if (empty($this->positionGroups)) {
            $this->line('No position groups available, skipping conditions...');

            return;
        }

        foreach ($this->positionGroups as $group) {
            if (rand(0, 1) && $group->conditions()->count() === 0) {
                $group->conditions()->create([
                    'type' => PositionGroupCondition::TYPE_ON_SINGLE_AIRFIELD,
                    'required_hours' => rand(10, 50),
                    'positions' => ['EGLL_%', 'EGKK_%'],
                ]);
            }
        }
        $this->line('Position group conditions seeded.');
    }

    private function seedAirportPositions(): void
    {
        $airports = Airport::limit(5)->get();
        if ($airports->count() === 0 || empty($this->positions)) {
            $this->line('No airports or positions available, skipping airport positions...');

            return;
        }

        foreach ($airports as $airport) {
            $positionsToAttach = array_slice($this->positions, 0, rand(1, 3));
            $positionIds = array_map(fn ($pos) => $pos->id, $positionsToAttach);
            $airport->positions()->syncWithoutDetaching($positionIds);
        }
        $this->line('Airport positions seeded.');
    }

    private function seedAccountEndorsements(): void
    {
        if (empty($this->accounts) || (empty($this->positions) && empty($this->positionGroups))) {
            $this->line('No accounts or endorsable items, skipping endorsements...');

            return;
        }

        foreach (array_slice($this->accounts, 0, 15) as $account) {
            if ($account->endorsements()->count() === 0) {
                // Endorse with positions
                if (! empty($this->positions)) {
                    \App\Models\Mship\Account\Endorsement::factory()->create([
                        'account_id' => $account->id,
                        'endorsable_type' => Position::class,
                        'endorsable_id' => $this->positions[array_rand($this->positions)]->id,
                    ]);
                }
            }
        }
        $this->line('Account endorsements seeded.');
    }

    private function seedEndorsementRequests(): void
    {
        if (empty($this->accounts) || empty($this->positions)) {
            $this->line('No accounts or positions, skipping endorsement requests...');

            return;
        }

        foreach (array_slice($this->accounts, 0, 10) as $account) {
            \App\Models\Mship\Account\EndorsementRequest::factory()->create([
                'account_id' => $account->id,
                'endorsable_type' => Position::class,
                'endorsable_id' => $this->positions[array_rand($this->positions)]->id,
            ]);
        }
        $this->line('Endorsement requests seeded.');
    }

    private function seedFeedbackForms(): void
    {
        if (Form::count() > 0) {
            $this->line('Feedback forms already exist, skipping...');

            return;
        }

        $contacts = \App\Models\Contact::limit(3)->get();
        if ($contacts->count() === 0) {
            $this->line('No contacts available for feedback forms, skipping...');

            return;
        }

        foreach ($contacts as $contact) {
            Form::create([
                'name' => 'Feedback Form for '.$contact->name,
                'slug' => \Illuminate\Support\Str::slug($contact->name),
                'contact_id' => $contact->id,
                'enabled' => true,
                'targeted' => true,
                'public' => true,
            ]);
        }
        $this->line('Feedback forms seeded.');
    }

    private function seedFeedbackQuestionTypes(): void
    {
        if (QuestionType::count() > 0) {
            $this->line('Question types already exist, skipping...');

            return;
        }

        QuestionType::create(['name' => 'Text', 'code' => 'text']);
        QuestionType::create(['name' => 'Rating', 'code' => 'rating']);
        $this->line('Question types seeded.');
    }

    private function seedFeedbackQuestions(): void
    {
        $forms = Form::all();
        $types = QuestionType::all();

        if ($forms->count() === 0 || $types->count() === 0) {
            $this->line('No forms or question types, skipping questions...');

            return;
        }

        foreach ($forms as $form) {
            if ($form->questions()->count() === 0) {
                for ($i = 1; $i <= 3; $i++) {
                    Question::create([
                        'form_id' => $form->id,
                        'type_id' => $types->random()->id,
                        'slug' => "question-{$i}",
                        'question' => "Sample question {$i}?",
                        'required' => (bool) rand(0, 1),
                        'sequence' => $i,
                        'permanent' => true,
                    ]);
                }
            }
        }
        $this->line('Feedback questions seeded.');
    }

    private function seedFeedback(): void
    {
        $forms = Form::all();
        if ($forms->count() === 0 || empty($this->accounts)) {
            $this->line('No forms or accounts, skipping feedback...');

            return;
        }

        foreach (array_slice($this->accounts, 0, 10) as $account) {
            Feedback::create([
                'form_id' => $forms->random()->id,
                'account_id' => $account->id,
                'submitter_account_id' => $this->accounts[array_rand($this->accounts)]->id,
            ]);
        }
        $this->line('Feedback seeded.');
    }

    private function seedFeedbackAnswers(): void
    {
        $feedbacks = Feedback::with('form.questions')->get();

        foreach ($feedbacks as $feedback) {
            foreach ($feedback->form->questions as $question) {
                if (Answer::where('feedback_id', $feedback->id)->where('question_id', $question->id)->doesntExist()) {
                    Answer::create([
                        'feedback_id' => $feedback->id,
                        'question_id' => $question->id,
                        'response' => 'Sample response to the question.',
                    ]);
                }
            }
        }
        $this->line('Feedback answers seeded.');
    }

    private function seedRoster(): void
    {
        if (empty($this->accounts)) {
            $this->line('No accounts available, skipping roster...');

            return;
        }

        foreach (array_slice($this->accounts, 0, 30) as $account) {
            Roster::firstOrCreate(['account_id' => $account->id]);
        }
        $this->line('Roster seeded.');
    }

    private function seedRosterHistory(): void
    {
        $rosterEntries = Roster::limit(5)->get();
        foreach ($rosterEntries as $entry) {
            if (RosterHistory::where('account_id', $entry->account_id)->doesntExist()) {
                RosterHistory::create([
                    'account_id' => $entry->account_id,
                    'original_created_at' => $entry->created_at,
                    'original_updated_at' => $entry->updated_at,
                    'removed_by' => $this->accounts[0]->id ?? null,
                ]);
            }
        }
        $this->line('Roster history seeded.');
    }

    private function seedWaitingListRetentionChecks(): void
    {
        $waitingListAccounts = \App\Models\Training\WaitingList\WaitingListAccount::limit(10)->get();
        
        if ($waitingListAccounts->count() === 0) {
            $this->line('No waiting list accounts available, skipping retention checks...');

            return;
        }

        foreach ($waitingListAccounts as $waitingListAccount) {
            if ($waitingListAccount->retentionChecks()->count() === 0) {
                \App\Models\Training\WaitingList\WaitingListRetentionCheck::factory()->create([
                    'waiting_list_account_id' => $waitingListAccount->id,
                ]);
            }
        }
        $this->line('Waiting list retention checks seeded.');
    }

    private function seedVtFacilities(): void
    {
        if (Facility::count() > 0) {
            $this->line('VT facilities already exist, skipping...');

            return;
        }

        Facility::factory()->count(3)->create();
        $this->line('VT facilities seeded.');
    }

    private function seedVtApplications(): void
    {
        $facilities = Facility::all();
        if ($facilities->count() === 0 || empty($this->accounts)) {
            $this->line('No facilities or accounts, skipping VT applications...');

            return;
        }

        foreach (array_slice($this->accounts, 0, 10) as $account) {
            Application::factory()->create([
                'account_id' => $account->id,
                'facility_id' => $facilities->random()->id,
            ]);
        }
        $this->line('VT applications seeded.');
    }

    private function seedVtReferences(): void
    {
        $applications = Application::all();
        if ($applications->count() === 0 || empty($this->accounts)) {
            $this->line('No applications or accounts, skipping VT references...');

            return;
        }

        foreach ($applications as $application) {
            Reference::factory()->create([
                'application_id' => $application->id,
                'account_id' => $this->accounts[array_rand($this->accounts)]->id,
            ]);
        }
        $this->line('VT references seeded.');
    }

    private function seedCts(): void
    {
        if (empty($this->accounts)) {
            $this->line('No accounts available for CTS, skipping...');

            return;
        }

        // Seed CTS members linked to accounts
        $ctsMembers = [];
        foreach (array_slice($this->accounts, 0, 20) as $account) {
            $member = \App\Models\Cts\Member::factory()->create([
                'id' => $account->id,
                'cid' => $account->id,
            ]);
            $ctsMembers[] = $member;
        }

        if (empty($ctsMembers)) {
            $this->line('No CTS members created, skipping CTS data...');

            return;
        }

        // Seed CTS positions
        $ctsPositions = \App\Models\Cts\Position::factory()->count(10)->create()->all();

        // Seed CTS sessions linked to members (only if we have at least 2 members)
        if (count($ctsMembers) >= 2) {
            foreach (array_slice($ctsMembers, 0, min(10, count($ctsMembers))) as $index => $student) {
                // Ensure mentor is different from student
                $mentorIndex = ($index + 1) % count($ctsMembers);
                $mentor = $ctsMembers[$mentorIndex];
                \App\Models\Cts\Session::factory()->create([
                    'student_id' => $student->id,
                    'mentor_id' => $mentor->id,
                    'position' => ! empty($ctsPositions) ? $ctsPositions[array_rand($ctsPositions)]->name : 'EGLL_APP',
                ]);
            }
        }

        // Seed CTS bookings linked to members
        foreach (array_slice($ctsMembers, 0, 15) as $member) {
            \App\Models\Cts\Booking::factory()->create([
                'member_id' => $member->id,
                'position' => ! empty($ctsPositions) ? $ctsPositions[array_rand($ctsPositions)]->name : 'EGKK_APP',
            ]);
        }

        // Seed CTS memberships linked to members
        foreach (array_slice($ctsMembers, 0, 15) as $member) {
            \App\Models\Cts\Membership::factory()->create([
                'member_id' => $member->id,
            ]);
        }

        // Seed exam setups (shared resources for exams)
        \App\Models\Cts\ExamSetup::factory()->count(5)->create();

        // Seed theory questions
        \App\Models\Cts\TheoryQuestion::factory()->count(20)->create();

        // Seed theory results linked to members
        foreach (array_slice($ctsMembers, 0, 8) as $member) {
            \App\Models\Cts\TheoryResult::factory()->create([
                'student_id' => $member->id,
            ]);
        }

        // Seed practical results linked to members
        foreach (array_slice($ctsMembers, 0, 8) as $member) {
            \App\Models\Cts\PracticalResult::factory()->create([
                'student_id' => $member->id,
            ]);
        }

        // Seed exam bookings linked to members
        foreach (array_slice($ctsMembers, 0, 5) as $member) {
            \App\Models\Cts\ExamBooking::factory()->create([
                'student_id' => $member->id,
            ]);
        }

        // Seed availabilities linked to members
        foreach (array_slice($ctsMembers, 0, 10) as $member) {
            \App\Models\Cts\Availability::factory()->create([
                'student_id' => $member->id,
            ]);
        }

        $this->line('CTS data seeded with proper connections to members.');
    }

    private function seedDiscordRoleRules(): void
    {
        if (\App\Models\Discord\DiscordRoleRule::count() > 0) {
            $this->line('Discord role rules already exist, skipping...');

            return;
        }

        \App\Models\Discord\DiscordRoleRule::factory()->count(5)->create();
        $this->line('Discord role rules seeded.');
    }
}
