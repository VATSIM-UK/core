<?php

namespace App\Console\Commands\TeamSpeak;

use App\Console\Commands\Command;
use App\Exceptions\TeamSpeak\ClientKickedFromServerException;
use App\Exceptions\TeamSpeak\RegistrationNotFoundException;
use App\Libraries\TeamSpeak;
use App\Models\Mship\Account;
use App\Models\TeamSpeak\Registration;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TeamSpeak3_Adapter_ServerQuery_Exception;

abstract class TeamSpeakCommand extends Command
{
    /**
     * @var TeamSpeakDaemon|TeamSpeakManager The console command object, used by static event-driven functions.
     */
    protected static $command;

    /**
     * @var int The TeamSpeak DBID of the current member being processed.
     */
    protected $currentMember = null;

    /**
     * Run the console command.
     *
     * In order to avoid self::$command being overwritten when each inherited class is constructed, the assignment
     * must be made here, when it is known that this is the command to be run.
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        self::$command = $this;

        return parent::run($input, $output);
    }

    /**
     * Handling for a serverquery exception thrown by the TeamSpeak framework.
     *
     * @param  Account  $account
     */
    protected static function handleServerQueryException(TeamSpeak3_Adapter_ServerQuery_Exception $e, Account $account = null)
    {
        if ($e->getCode() === TeamSpeak::CLIENT_INVALID_ID) {
            self::$command->log('Invalid client ID.');
        } elseif ($e->getCode() === TeamSpeak::PERMISSIONS_CLIENT_INSUFFICIENT) {
            self::$command->log('Insufficient permissions to perform this action on this member.');
        } elseif ($e->getMessage() == 'duplicate entry') {
            if ($account) {
                self::$command->log('Member already has server group. ['.$account->real_name.' '.$account->id.']');
            }
            self::$command->log('Member already has server group.');
        } else {
            self::handleException($e);
        }
    }

    /**
     * Handling for all exceptions.
     */
    protected static function handleException(Exception $e)
    {
        if (get_class($e) === ClientKickedFromServerException::class) {
            self::$command->log('Kicked from server.');

            return;
        } elseif (get_class($e) === RegistrationNotFoundException::class) {
            self::$command->log('Registration not found.');

            return;
        }

        self::$command->log('Caught: '.get_class($e));
        self::$command->log($e->getTraceAsString());

        $member = Registration::where('dbid', self::$command->currentMember)->first();
        if (! is_null(self::$command->currentMember) && ! is_null($member)) {
            $member = $member->account;
        } else {
            return;
        }

        $description = $member->name_first.' '
            .$member->name_last.' ('
            .$member->id.')';
        $message = 'TeaMan has encountered a previously unhandled error:'.PHP_EOL.PHP_EOL
            .'Client: '.$description.PHP_EOL.PHP_EOL
            .'Stack trace:'.PHP_EOL.PHP_EOL
            .$e->getTraceAsString()
            .PHP_EOL.'Error message: '.$e->getMessage().PHP_EOL;
        self::$command->log($message);
    }
}
