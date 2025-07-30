<?php

namespace App\Console\Commands\Development;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateLocalAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'local:create-account
                            {--cid= : Specific CID for the account (optional, random if not provided)}
                            {--first-name= : First name (optional, fake if not provided)}
                            {--last-name= : Last name (optional, fake if not provided)}
                            {--email= : Email address (optional, fake if not provided)}
                            {--qualification=* : ATC/Pilot qualifications (e.g., OBS, S1, S2, S3, C1, C2, C3, P0, P1, P2, P3, P4)}
                            {--state= : Membership state (division, region, international, visiting, transferring)}
                            {--list-qualifications : List all available qualifications}
                            {--list-states : List all available membership states}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test Account with specified qualifications and membership state (LOCAL ENVIRONMENT ONLY)';

    // ===============================================
    // QUALIFICATION MAPPINGS
    // ===============================================

    /**
     * Available ATC qualifications mapping (user input => database code)
     */
    private const ATC_QUALIFICATIONS = [
        'obs' => 'OBS',
        's1' => 'S1',
        's2' => 'S2',
        's3' => 'S3',
        'c1' => 'C1',
        'c2' => 'C2',
        'c3' => 'C3',
    ];

    /**
     * Available pilot qualifications mapping (user input => VATSIM network value)
     */
    private const PILOT_QUALIFICATIONS = [
        'p0' => 0,  // Basic Pilot
        'p1' => 1,  // Private Pilot
        'p2' => 2,  // Instrument Rating
        'p3' => 4,  // Commercial
        'p4' => 8,  // Airline Transport Pilot
        'p5' => 16, // Flight Instructor
    ];

    // ===============================================
    // MEMBERSHIP STATE MAPPINGS
    // ===============================================

    /**
     * Available membership states mapping (user input => database code)
     */
    private const MEMBERSHIP_STATES = [
        'division' => 'DIVISION',
        'region' => 'REGION',
        'international' => 'INTERNATIONAL',
        'visiting' => 'VISITING',
        'transferring' => 'TRANSFERRING',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->isLocalEnvironment()) {
            return $this->exitWithError('This command can only be executed in the local environment for security reasons.');
        }

        if ($this->shouldListQualifications()) {
            $this->listQualifications();
            return 0;
        }

        if ($this->shouldListStates()) {
            $this->listStates();
            return 0;
        }

        return $this->createAccountWithTransaction();
    }

    /**
     * Check if running in local environment
     */
    private function isLocalEnvironment(): bool
    {
        return app()->environment('local');
    }

    /**
     * Check if user wants to list qualifications
     */
    private function shouldListQualifications(): bool
    {
        return $this->option('list-qualifications');
    }

    /**
     * Check if user wants to list states
     */
    private function shouldListStates(): bool
    {
        return $this->option('list-states');
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
     * Create account within database transaction
     */
    private function createAccountWithTransaction(): int
    {
        $this->info('Creating local test account...');

        DB::beginTransaction();

        try {
            $account = $this->createAccount();
            $this->outputAccountCreated($account);

            $this->processQualifications($account);
            $this->processMembershipState($account);

            DB::commit();

            $this->outputSuccessAndSummary($account);
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exitWithError("Failed to create account: {$e->getMessage()}");
        }
    }

    /**
     * Get CID from option or generate a new one
     */
    private function getOrGenerateCid(): int
    {
        return $this->option('cid') ?: $this->generateValidCid();
    }

    /**
     * Validate that CID doesn't already exist
     */
    private function validateCidDoesNotExist(int $cid): void
    {
        if (Account::find($cid)) {
            throw new \Exception("Account with CID {$cid} already exists");
        }
    }

    /**
     * Output account creation success message
     */
    private function outputAccountCreated(Account $account): void
    {
        $this->info("✅ Created account: {$account->real_name} (CID: {$account->id})");
    }

    /**
     * Process qualifications if specified
     */
    private function processQualifications(Account $account): void
    {
        $qualifications = $this->option('qualification');
        if (!empty($qualifications)) {
            $this->addQualifications($account, $qualifications);
        }
    }

    /**
     * Process membership state if specified
     */
    private function processMembershipState(Account $account): void
    {
        $state = $this->option('state');
        if ($state) {
            $this->addMembershipState($account, $state);
        }
    }

    /**
     * Output success message and account summary
     */
    private function outputSuccessAndSummary(Account $account): void
    {
        $this->newLine();
        $this->info('🎉 Account created successfully!');
        $this->displayAccountSummary($account);
    }

    /**
     * Create the base account using factory with validation
     */
    private function createAccount(): Account
    {
        $cid = $this->getOrGenerateCid();
        $this->validateCidDoesNotExist($cid);

        return Account::factory()->create($this->getAccountAttributes($cid));
    }

    /**
     * Get account attributes for factory, using provided options or factory defaults
     */
    private function getAccountAttributes(int $cid): array
    {
        $attributes = ['id' => $cid, 'joined_at' => now()];

        // Only override factory defaults if user provided explicit values
        if ($firstName = $this->option('first-name')) {
            $attributes['name_first'] = $firstName;
        }

        if ($lastName = $this->option('last-name')) {
            $attributes['name_last'] = $lastName;
        }

        if ($email = $this->option('email')) {
            $attributes['email'] = $email;
        }

        return $attributes;
    }

    /**
     * Generate a valid VATSIM CID (7-digit number that doesn't exist)
     */
    private function generateValidCid(): int
    {
        // Use a more efficient approach with factory-style generation
        $attempts = 0;
        $maxAttempts = 100;

        do {
            $cid = fake()->numberBetween(1_000_000, 9_999_999);
            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new \Exception('Unable to generate unique CID after multiple attempts');
            }
        } while (Account::find($cid));

        return $cid;
    }

    /**
     * Add qualifications to the account
     */
    private function addQualifications(Account $account, array $qualifications): void
    {
        collect($qualifications)
            ->map(fn($code) => strtolower(trim($code)))
            ->each(fn($qualCode) => $this->addSingleQualification($account, $qualCode));
    }

    /**
     * Add a single qualification to the account
     */
    private function addSingleQualification(Account $account, string $qualCode): void
    {
        $qualification = $this->findQualification($qualCode);

        if ($qualification) {
            $account->addQualification($qualification);
            $this->outputQualificationAdded($qualification);
        } else {
            $this->outputQualificationNotFound($qualCode);
        }
    }

    /**
     * Find qualification by code
     */
    private function findQualification(string $qualCode): ?Qualification
    {
        // Try ATC qualifications first
        if (isset(self::ATC_QUALIFICATIONS[$qualCode])) {
            return Qualification::code(self::ATC_QUALIFICATIONS[$qualCode])->first();
        }

        // Try pilot qualifications
        if (isset(self::PILOT_QUALIFICATIONS[$qualCode])) {
            $vatsimValue = self::PILOT_QUALIFICATIONS[$qualCode];
            return Qualification::ofType('pilot')->networkValue($vatsimValue)->first();
        }

        // Try exact code match as fallback
        return Qualification::code(strtoupper($qualCode))->first();
    }

    /**
     * Output qualification added message
     */
    private function outputQualificationAdded(Qualification $qualification): void
    {
        $this->info("✅ Added qualification: {$qualification->name}");
    }

    /**
     * Output qualification not found warning
     */
    private function outputQualificationNotFound(string $qualCode): void
    {
        $this->warn("⚠️  Unknown qualification: '{$qualCode}'");
        $this->warn('    Use --list-qualifications to see available options');
    }

    /**
     * Add membership state to the account
     */
    private function addMembershipState(Account $account, string $stateCode): void
    {
        $stateCode = strtolower(trim($stateCode));

        if (!isset(self::MEMBERSHIP_STATES[$stateCode])) {
            $this->outputStateNotFound($stateCode);
            return;
        }

        $state = State::findByCode(self::MEMBERSHIP_STATES[$stateCode]);
        if (!$state) {
            $this->warn("⚠️  State '{$stateCode}' not found in database");
            return;
        }

        $this->addStateToAccount($account, $state, $stateCode);
    }

    /**
     * Add state to account with appropriate region/division
     */
    private function addStateToAccount(Account $account, State $state, string $stateCode): void
    {
        $region = $stateCode === 'division' ? 'EUR' : null;
        $division = $stateCode === 'division' ? 'GBR' : null;

        $account->addState($state, $region, $division);
        $this->info("✅ Added membership state: {$state->name}");
    }

    /**
     * Output state not found warning
     */
    private function outputStateNotFound(string $stateCode): void
    {
        $this->warn("⚠️  Unknown membership state: '{$stateCode}'");
        $this->warn('    Use --list-states to see available options');
    }

    /**
     * List all available qualifications
     */
    private function listQualifications(): void
    {
        $this->listAtcQualifications();
        $this->newLine();
        $this->listPilotQualifications();
        $this->newLine();
        $this->info('You can also use exact qualification codes like: OBS, S1, S2, S3, C1, C2, C3');
    }

    /**
     * List ATC qualifications
     */
    private function listAtcQualifications(): void
    {
        $this->info('Available ATC Qualifications:');
        collect(self::ATC_QUALIFICATIONS)
            ->map(fn($code, $short) => [
                'short' => $short,
                'code' => $code,
                'name' => Qualification::code($code)->first()?->name_long ?? 'Not found'
            ])
            ->each(fn($qual) => $this->line("  {$qual['short']} -> {$qual['code']} ({$qual['name']})"));
    }

    /**
     * List pilot qualifications
     */
    private function listPilotQualifications(): void
    {
        $this->info('Available Pilot Qualifications:');
        collect(self::PILOT_QUALIFICATIONS)
            ->map(fn($vatsimValue, $short) => [
                'short' => $short,
                'name' => Qualification::ofType('pilot')->networkValue($vatsimValue)->first()?->name_long ?? 'Not found'
            ])
            ->each(fn($qual) => $this->line("  {$qual['short']} -> {$qual['name']}"));
    }

    /**
     * List all available membership states
     */
    private function listStates(): void
    {
        $this->info('Available Membership States:');
        collect(self::MEMBERSHIP_STATES)
            ->map(fn($code, $short) => [
                'short' => $short,
                'code' => $code,
                'state' => State::findByCode($code)
            ])
            ->each(function ($item) {
                $state = $item['state'];
                $name = $state?->name ?? 'Not found';
                $type = $state ? "({$state->type})" : '';
                $this->line("  {$item['short']} -> {$item['code']} {$type} - {$name}");
            });
    }

    /**
     * Display a summary of the created account
     */
    private function displayAccountSummary(Account $account): void
    {
        $this->table(['Property', 'Value'], $this->getAccountSummaryData($account));
    }

    /**
     * Get account summary data for table display
     */
    private function getAccountSummaryData(Account $account): array
    {
        return [
            ['CID', $account->id],
            ['Name', $account->real_name],
            ['Email', $account->email],
            ['Qualifications', $account->qualifications->pluck('name')->join(', ') ?: 'None'],
            ['Primary State', $account->primary_state?->name ?: 'None'],
            ['All States', $account->states->pluck('name')->join(', ') ?: 'None'],
        ];
    }
}
