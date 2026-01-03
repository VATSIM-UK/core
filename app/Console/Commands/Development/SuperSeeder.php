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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class SuperSeeder extends Command
{
    use WithoutModelEvents;

    protected $signature = 'db:super-seed {--tables=* : Specific tables to seed}';

    protected $description = 'Seeds all tables with realistic-looking data for development purposes.';

    private array $accounts = [];

    private array $positions = [];

    private array $positionGroups = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if a table exists on the CTS connection
     */
    private function ctsTableExists(string $table): bool
    {
        try {
            return Schema::connection('cts')->hasTable($table);
        } catch (\Exception $e) {
            // If checking fails (no connection or misconfigured), treat as missing
            return false;
        }
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

        // Disable all model events to prevent listeners from creating accounts
        \Illuminate\Database\Eloquent\Model::unsetEventDispatcher();

        try {
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
        } finally {
            // Re-enable the dispatcher
            \Illuminate\Database\Eloquent\Model::setEventDispatcher(app('events'));
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

        // Seed airports
        $this->info('Seeding airports...');
        $this->seedAirports();

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
        if (WaitingList::count() === 0) {
            $this->call(WaitingListStressSeeder::class);
        } else {
            $this->line('Waiting lists already exist, skipping seeding...');
        }
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

    private function seedAirports(): void
    {
        $count = Airport::count();
        if ($count >= 10) {
            $this->line("Airports already exist ({$count} exist), skipping...");

            return;
        }

        // Create some common UK airports for testing
        $airports = [
            ['icao' => 'EGLL', 'iata' => 'LHR', 'name' => 'London Heathrow'],
            ['icao' => 'EGKK', 'iata' => 'LGW', 'name' => 'London Gatwick'],
            ['icao' => 'EGSS', 'iata' => 'STN', 'name' => 'London Stansted'],
            ['icao' => 'EGLC', 'iata' => 'LCY', 'name' => 'London City'],
            ['icao' => 'EGMD', 'iata' => 'MSE', 'name' => 'Manston'],
            ['icao' => 'EGBB', 'iata' => 'BHX', 'name' => 'Birmingham'],
            ['icao' => 'EGNX', 'iata' => 'EMA', 'name' => 'East Midlands'],
            ['icao' => 'EGNT', 'iata' => 'NCL', 'name' => 'Newcastle'],
            ['icao' => 'EGFF', 'iata' => 'CWL', 'name' => 'Cardiff'],
            ['icao' => 'EGPF', 'iata' => 'GLA', 'name' => 'Glasgow'],
        ];

        foreach ($airports as $airport) {
            Airport::firstOrCreate(
                ['icao' => $airport['icao']],
                [
                    'iata' => $airport['iata'],
                    'name' => $airport['name'],
                ]
            );
        }

        $this->line('10 airports seeded.');
    }

    private function seedAccounts(): void
    {
        $count = Account::count();

        // Skip account creation entirely if any accounts exist
        // This prevents creating more accounts on subsequent runs
        if ($count > 0) {
            $this->line("Accounts already exist ({$count} total), skipping creation...");
            $this->accounts = Account::limit(50)->get()->all();

            return;
        }

        // Only create accounts if the table is completely empty
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

        // Create VT facilities manually since factory is not available
        for ($i = 0; $i < 3; $i++) {
            Facility::create([
                'name' => fake()->company(),
                'description' => fake()->paragraph(),
                'can_transfer' => rand(0, 1),
                'can_visit' => rand(0, 1),
                'training_required' => rand(0, 1),
                'training_team' => fake()->randomElement(['atc', 'pilot']),
                'training_spaces' => rand(5, 20),
                'stage_statement_enabled' => 1,
                'stage_reference_enabled' => 1,
                'stage_reference_quantity' => 2,
                'stage_checks' => 0,
                'auto_acceptance' => 0,
                'open' => 1,
                'public' => 1,
            ]);
        }
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
            Application::create([
                'account_id' => $account->id,
                'facility_id' => $facilities->random()->id,
                'type' => fake()->randomElement([Application::TYPE_VISIT, Application::TYPE_TRANSFER]),
                'training_team' => fake()->randomElement(['atc', 'pilot']),
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
            Reference::create([
                'application_id' => $application->id,
                'account_id' => $this->accounts[array_rand($this->accounts)]->id,
                'email' => fake()->email(),
                'relationship' => fake()->word(),
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

        // Ensure CTS connection has necessary tables before attempting any operations
        if (! $this->ctsTableExists('members') || ! $this->ctsTableExists('positions')) {
            $this->line('CTS schema not available or missing essential tables, skipping CTS seeding...');

            return;
        }

        // Seed CTS members linked to accounts (only if they don't already exist)
        // Only use the first 20 accounts to avoid member proliferation
        $ctsMembers = [];
        foreach (array_slice($this->accounts, 0, 20) as $account) {
            // Check if member already exists before creating
            $member = \App\Models\Cts\Member::find($account->id);
            if (! $member) {
                $member = \App\Models\Cts\Member::factory()->create([
                    'id' => $account->id,
                    'cid' => $account->id,
                ]);
            }
            $ctsMembers[] = $member;
        }

        if (empty($ctsMembers)) {
            $this->line('No CTS members available, skipping CTS data...');

            return;
        }

        // Seed CTS positions (only if they don't already exist)
        $positionCount = \App\Models\Cts\Position::count();
        if ($positionCount === 0) {
            $ctsPositions = \App\Models\Cts\Position::factory()->count(10)->create()->all();
        } else {
            $ctsPositions = \App\Models\Cts\Position::limit(10)->get()->all();
            $this->line("CTS positions already exist ({$positionCount} total), using existing...");
        }

        // Seed CTS sessions linked to members (only if we have at least 2 members and positions)
        if (count($ctsMembers) >= 2 && ! empty($ctsPositions)) {
            foreach (array_slice($ctsMembers, 0, min(10, count($ctsMembers))) as $index => $student) {
                // Check if session already exists for this student
                if (\App\Models\Cts\Session::where('student_id', $student->id)->doesntExist()) {
                    try {
                        // Ensure mentor is different from student
                        $mentorIndex = ($index + 1) % count($ctsMembers);
                        $mentor = $ctsMembers[$mentorIndex];
                        $position = $ctsPositions[array_rand($ctsPositions)];
                        \App\Models\Cts\Session::factory()->create([
                            'student_id' => $student->id,
                            'mentor_id' => $mentor->id,
                            'position' => $position->name ?? $position,
                        ]);
                    } catch (\Exception $e) {
                        $this->line("Warning: Could not create CTS session for member {$student->id}: {$e->getMessage()}");
                    }
                }
            }
        } elseif (count($ctsMembers) >= 2) {
            $this->line('No CTS positions available, skipping sessions...');
        }

        // Seed CTS bookings linked to members (only if they don't already exist)
        if (! empty($ctsPositions)) {
            foreach (array_slice($ctsMembers, 0, 15) as $member) {
                if (\App\Models\Cts\Booking::where('member_id', $member->id)->doesntExist()) {
                    try {
                        $position = $ctsPositions[array_rand($ctsPositions)];
                        \App\Models\Cts\Booking::factory()->create([
                            'member_id' => $member->id,
                            'position' => $position->name ?? $position,
                        ]);
                    } catch (\Exception $e) {
                        // Skip on error
                    }
                }
            }
        } else {
            $this->line('No CTS positions available, skipping bookings...');
        }

        // Seed CTS memberships linked to members (only if they don't already exist)
        foreach (array_slice($ctsMembers, 0, 15) as $member) {
            if (\App\Models\Cts\Membership::where('member_id', $member->id)->doesntExist()) {
                try {
                    \App\Models\Cts\Membership::factory()->create([
                        'member_id' => $member->id,
                    ]);
                } catch (\Exception $e) {
                    // Skip on error
                }
            }
        }

        // Seed exam setups (shared resources for exams, only if empty)
        if (Schema::connection('cts')->hasTable('exam_setup') && \App\Models\Cts\ExamSetup::count() === 0) {
            try {
                \App\Models\Cts\ExamSetup::factory()->count(5)->create();
            } catch (\Exception $e) {
                $this->line("Warning: Could not create exam setups: {$e->getMessage()}");
            }
        }

        // Seed theory questions (shared resources, only if empty)
        if (Schema::connection('cts')->hasTable('theory_questions') && \App\Models\Cts\TheoryQuestion::count() === 0) {
            try {
                \App\Models\Cts\TheoryQuestion::factory()->count(20)->create();
            } catch (\Exception $e) {
                $this->line("Warning: Could not create theory questions: {$e->getMessage()}");
            }
        }

        // Seed theory results linked to members (only if they don't already exist)
        if (Schema::connection('cts')->hasTable('theory_results')) {
            foreach (array_slice($ctsMembers, 0, 8) as $member) {
                if (\App\Models\Cts\TheoryResult::where('student_id', $member->id)->doesntExist()) {
                    try {
                        \App\Models\Cts\TheoryResult::factory()->create([
                            'student_id' => $member->id,
                        ]);
                    } catch (\Exception $e) {
                        // Skip on error
                    }
                }
            }
        }

        // Seed practical results linked to members (only if they don't already exist)
        if (Schema::connection('cts')->hasTable('practical_results')) {
            foreach (array_slice($ctsMembers, 0, 8) as $member) {
                if (\App\Models\Cts\PracticalResult::where('student_id', $member->id)->doesntExist()) {
                    try {
                        \App\Models\Cts\PracticalResult::factory()->create([
                            'student_id' => $member->id,
                        ]);
                    } catch (\Exception $e) {
                        // Skip on error
                    }
                }
            }
        }

        // Seed exam bookings linked to members (only if they don't already exist)
        if (Schema::connection('cts')->hasTable('exam_bookings')) {
            foreach (array_slice($ctsMembers, 0, 5) as $member) {
                if (\App\Models\Cts\ExamBooking::where('student_id', $member->id)->doesntExist()) {
                    try {
                        \App\Models\Cts\ExamBooking::factory()->create([
                            'student_id' => $member->id,
                        ]);
                    } catch (\Exception $e) {
                        // Skip on error
                    }
                }
            }
        }

        // Seed availabilities linked to members (only if they don't already exist)
        if (Schema::connection('cts')->hasTable('availability')) {
            foreach (array_slice($ctsMembers, 0, 10) as $member) {
                if (\App\Models\Cts\Availability::where('student_id', $member->id)->doesntExist()) {
                    try {
                        \App\Models\Cts\Availability::factory()->create([
                            'student_id' => $member->id,
                        ]);
                    } catch (\Exception $e) {
                        // Skip on error
                    }
                }
            }
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
