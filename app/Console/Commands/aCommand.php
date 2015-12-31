<?php

namespace App\Console\Commands;

use Maknz\Slack\Client;
use Illuminate\Console\Command;

class aCommand extends Command {
    protected $slack = null;

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();

        // Configure slack for Cron!
        $settings = [
            "channel" => "wscronjobs",
            "link_names" => true,
        ];
        $this->slack = new Client("https://hooks.slack.com/services/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", $settings);
    }

    /**
     * Log a string to STDOUT.
     *
     * If STDOUT is piped/redirected, styling is removed.
     *
     * @param      $string The string to output.
     * @param null $style The styling to output.
     */
    protected function log($string, $style = null)
    {
        $style = posix_isatty(STDOUT) ? $style : null;

        $this->line($string, $style, OutputInterface::VERBOSITY_VERBOSE);
    }

    protected function sendSlackError($errorCode, $error){
        $slackMessage = "@here :exclamation: *This is important*.";
        $slackMessage.= " ".$this->getName(). " (ERROR_".$errorCode.") -> ";
        $slackMessage.= $error;

        $this->slack->send($slackMessage);
    }

    protected function sendSlackSuccess($message="Has run successfully."){
        $slackMessage = "*".$this->getName(). "* -> ";

        if ($message) {
            $slackMessage.= "_".$message."_";
        }

        $this->slack->send($slackMessage);
    }
}
