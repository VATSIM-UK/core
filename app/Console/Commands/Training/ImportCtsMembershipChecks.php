<?php

namespace App\Console\Commands\Training;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCtsMembershipChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waiting-lists:import-cts-membership-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import memberships_check from CTS db to training_waiting_list_retention_checks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /* training_waiting_list_retention_checks
        NEW TABLE = Schema::create('training_waiting_list_retention_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('waiting_list_account_id');
            $table->string('token');
            $table->dateTime('expires_at');
            $table->dateTime('response_at')->nullable();
            $table->string('status');
            $table->dateTime('email_sent_at');
            $table->dateTime('removal_actioned_at')->nullable();
            $table->timestamps();
        });
        */

        /* OLD TABLE (cts.memberships_check):

        Field 	Type 	Null 	Key 	Default 	Extra
        id 	int 	NO 	PRI 	NULL 	auto_increment
        rts_id 	int 	NO 		NULL
        member_id 	int 	NO 		NULL
        code 	varchar(14) 	NO 		NULL
        date_requested 	timestamp 	YES 		NULL
        requested_email 	tinyint(1) 	NO 		NULL
        reminder_email 	tinyint(1) 	NO 		NULL
        date_clicked 	timestamp 	YES 		NULL
        date_expires 	timestamp 	YES 		NULL
        expired_email 	tinyint(1) 	NO 		NULL
        status 	enum('A','E','U') 	NO 		NULL (active, expired, used)
        */

        $records = DB::connection('cts')
            ->table('memberships_check')
            ->join('members', 'memberships_check.member_id', '=', 'members.id')
            ->select([
                'memberships_check.code',
                'memberships_check.date_expires',
                'memberships_check.date_clicked',
                'memberships_check.status',
                'memberships_check.date_requested',
                'members.cid as cid',
            ])
            ->where('memberships_check.status', '!=', 'E')
            ->whereIn('members.cid', function ($query) {
                $query->select('account_id')
                    ->from('training_waiting_list_accounts')
                    ->whereNull('deleted_at');
            })
            ->get();

        foreach ($records as $record) {
            switch ($record->status) {
                case 'A':
                    $newStatus = 'pending';
                    break;
                case 'U':
                    $newStatus = 'used';
                    break;
                case 'E':
                    $newStatus = 'expired';
                    break;
                default:
                    $newStatus = $record->status; // fallback, should not happen
            }

            DB::table('training_waiting_list_retention_checks')->insert([
                'waiting_list_account_id' => $record->cid,
                'token' => $record->code,
                'expires_at' => $record->date_expires,
                'response_at' => $record->date_clicked,
                'status' => $newStatus,
                'email_sent_at' => $record->date_requested,
                'removal_actioned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info('Data imported successfully.');
    }
}
