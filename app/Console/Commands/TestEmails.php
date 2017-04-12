<?php

namespace App\Console\Commands;

use App\Models\Sys\Token;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Feedback\Feedback;
use App\Notifications\Mship\BanCreated;
use App\Notifications\Mship\BanModified;
use App\Notifications\Mship\BanRepealed;
use App\Notifications\Mship\WelcomeMember;
use App\Notifications\Mship\SlackInvitation;
use App\Notifications\Mship\FeedbackReceived;
use App\Notifications\Mship\EmailVerification;
use App\Modules\Visittransfer\Models\Reference;
use App\Modules\Visittransfer\Models\Application;
use App\Notifications\Mship\Security\TemporaryPassword;
use App\Notifications\Mship\Security\ForgottenPasswordLink;
use App\Modules\Visittransfer\Notifications\ApplicationReview;
use App\Modules\Visittransfer\Notifications\ApplicationAccepted;
use App\Modules\Visittransfer\Notifications\ApplicationStatusChanged;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceRequest;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceAccepted;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceRejected;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceCancelled;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceSubmitted;

/**
 * Experimental class used for generating emails to mailtrap.io
 * in order to visually test them and verify the end result.
 */
class TestEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:emails {--revert}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a copy of every email template to a test user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (\App::environment('production')) {
            $this->log('ERROR: this command cannot be run in production!', 'error');

            return;
        } elseif (env('MAIL_HOST') !== 'mailtrap.io') {
            $this->log('ERROR: you should be using mailtrap.io before running this command!', 'error');

            return;
        } elseif (!$this->confirm(
            'This command will make changes to the database that must be manually reversed.'.PHP_EOL
            .' Do you wish to continue?'
        )) {
            return;
        }

        $this->log('testAccount');
        $id = 1;
        $ids = Account::orderBy('id')->pluck('id')->toArray();
        while (true) {
            if (!in_array($id, $ids)) {
                break;
            } else {
                $id++;
            }
        }
        $testAccount = new Account();
        $testAccount->id = $id;
        $testAccount->name_first = 'Test';
        $testAccount->name_last = 'Account';
        $testAccount->email = 'test-account@example.com';
        $testAccount->save();

        $this->log('testEmail');
        $testEmail = new Account\Email();
        $testEmail->account_id = $testAccount->id;
        $testEmail->email = 'test-email@example.com';
        $testEmail->save();

        $this->log('testTokenEmailVerify');
        $testTokenEmailVerify = Token::generate('mship_account_email_verify', false, $testEmail);
        $this->log('testTokenSecurityReset');
        $testTokenSecurityReset = Token::generate('mship_account_security_reset', false, $testAccount);

        $this->log('testApplication');
        $testApplication = new Application();
        $testApplication->facility_id = 1;
        $testApplication->save();
        $this->log('testReference');
        $testReference = new Reference();
        $testReference->account_id = $testAccount->id;
        $testReference->application_id = $testApplication->id;
        $testReference->save();

        $this->log('testBan');
        $testBan = new Ban();
        $testBan->save();

        $this->log('testFeedback');
        $testFeedback = new Feedback();
        $testFeedback->form_id = 1;
        $testFeedback->save();

        // main
        $testAccount->notify(new BanCreated($testBan));
        $testAccount->notify(new BanModified($testBan));
        $testAccount->notify(new BanRepealed($testBan));
        $testAccount->notify(new EmailVerification($testEmail, $testTokenEmailVerify));
        $testAccount->notify(new FeedbackReceived($testFeedback));
        $testAccount->notify(new SlackInvitation());
        $testAccount->notify(new WelcomeMember());
        $testAccount->notify(new ForgottenPasswordLink($testTokenSecurityReset));
        $testAccount->notify(new TemporaryPassword('test_password'));

        // visiting/transfer
        $testAccount->notify(new ApplicationAccepted($testApplication));
        $testAccount->notify(new ApplicationReferenceAccepted($testReference));
        $testReference->notify(new ApplicationReferenceCancelled($testReference));
        $testAccount->notify(new ApplicationReferenceRejected($testReference));
        $testReference->notify(new ApplicationReferenceRequest($testReference));
        $testAccount->notify(new ApplicationReferenceSubmitted($testReference));
        $testAccount->notify(new ApplicationReview($testApplication));
        $testAccount->notify(new ApplicationStatusChanged($testApplication));
    }
}
