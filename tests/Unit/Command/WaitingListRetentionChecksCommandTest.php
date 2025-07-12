<?php

namespace Tests\Unit\Command;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionChecksCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_imports_retention_checks_from_cts_and_maps_status_correctly()
    {
        // Setup: create CTS tables and insert test data
        DB::connection('cts')->statement('CREATE TABLE IF NOT EXISTS membership_checks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rts_id INT NOT NULL,
            member_id INT NOT NULL,
            code VARCHAR(14) NOT NULL,
            date_requested TIMESTAMP NULL,
            requested_email TINYINT(1) NOT NULL,
            reminder_email TINYINT(1) NOT NULL,
            date_clicked TIMESTAMP NULL,
            date_expires TIMESTAMP NULL,
            expired_email TINYINT(1) NOT NULL,
            status ENUM("active", "expired", "used") NOT NULL
        )');
        DB::connection('cts')->statement('CREATE TABLE IF NOT EXISTS members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cid INT NOT NULL,
            old_rts_id INT NOT NULL DEFAULT 0,
            joined_div TIMESTAMP NULL DEFAULT NULL
        )');
        DB::connection('cts')->table('members')->insert([
            ['id' => 1, 'cid' => 1, 'old_rts_id' => 0, 'joined_div' => now()],
            ['id' => 2, 'cid' => 2, 'old_rts_id' => 0, 'joined_div' => now()],
            ['id' => 3, 'cid' => 3, 'old_rts_id' => 0, 'joined_div' => now()],
        ]);
        DB::connection('cts')->table('membership_checks')->insert([
            [
                'rts_id' => 1,
                'member_id' => 1,
                'code' => 'TESTCODE',
                'date_requested' => now(),
                'requested_email' => 1,
                'reminder_email' => 0,
                'date_clicked' => null,
                'date_expires' => now()->addDays(7),
                'expired_email' => 0,
                'status' => 'active',
            ],
            [
                'rts_id' => 2,
                'member_id' => 2,
                'code' => 'USEDCODE',
                'date_requested' => now(),
                'requested_email' => 1,
                'reminder_email' => 0,
                'date_clicked' => now(),
                'date_expires' => now()->addDays(7),
                'expired_email' => 0,
                'status' => 'used',
            ],
            [
                'rts_id' => 3,
                'member_id' => 3,
                'code' => 'EXPIREDCODE',
                'date_requested' => now(),
                'requested_email' => 1,
                'reminder_email' => 0,
                'date_clicked' => null,
                'date_expires' => now()->subDays(1),
                'expired_email' => 0,
                'status' => 'expired',
            ]
        ]);

        // Setup: create local accounts for matching
        DB::table('training_waiting_list_account')->insert([
            ['id' => 1, 'account_id' => 1, 'list_id' => 1, 'deleted_at' => null],
            ['id' => 2, 'account_id' => 2, 'list_id' => 1, 'deleted_at' => null],
            ['id' => 3, 'account_id' => 3, 'list_id' => 1, 'deleted_at' => null],
        ]);

        // Run the import command
        $this->artisan('waiting-lists:import-cts-membership-checks')
            ->expectsOutput('Data imported successfully.')
            ->assertExitCode(0);

        // Assert correct mapping and import
        $this->assertDatabaseHas('training_waiting_list_retention_checks', [
            'waiting_list_account_id' => 1,
            'token' => 'TESTCODE',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('training_waiting_list_retention_checks', [
            'waiting_list_account_id' => 2,
            'token' => 'USEDCODE',
            'status' => 'used',
        ]);
        $this->assertDatabaseHas('training_waiting_list_retention_checks', [
            'waiting_list_account_id' => 3,
            'token' => 'EXPIREDCODE',
            'status' => 'expired',
        ]);
    }
}
