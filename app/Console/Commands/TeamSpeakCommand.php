<?php

namespace App\Console\Commands;

use Exception;
use App\Libraries\TeamSpeak;
use App\Models\TeamSpeak\Registration;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Exceptions\TeamSpeak\RegistrationNotFoundException;
use App\Exceptions\TeamSpeak\ClientKickedFromServerException;

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
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        self::$command = $this;

        return parent::run($input, $output);
    }

    /**
     * Handling for a serverquery exception thrown by the TeamSpeak framework.
     *
     * @param TeamSpeak3_Adapter_ServerQuery_Exception $e
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    protected static function handleServerQueryException(TeamSpeak3_Adapter_ServerQuery_Exception $e)
    {
        if ($e->getCode() === TeamSpeak::CLIENT_INVALID_ID) {
            self::$command->log('Invalid client ID.');
        } elseif ($e->getCode() === TeamSpeak::PERMISSIONS_CLIENT_INSUFFICIENT) {
            self::$command->log('Insufficient permissions to perform this action on this member.');
        } else {
            self::handleException($e);
        }
    }

    /**
     * Handling for all exceptions.
     *
     * @param \Exception $e
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
        if (!is_null(self::$command->currentMember) && !is_null($member)) {
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

        self::$command->sendSlackError('Exception processing client.', [
            'name' => $description,
        ]);
    }
}
