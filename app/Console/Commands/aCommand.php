<?php

namespace App\Console\Commands;

use App;
use Maknz\Slack\Client;
use Illuminate\Console\Command;

class aCommand extends Command {
    protected $slack = null;

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();

        // configure slack
        $settings = [
            'channel' => 'wscronjobs',
            'link_names' => true,
            'markdown_in_attachments' => ['pretext', 'text', 'title', 'fields', 'fallback'],
        ];
        $this->slack = new Client('https://hooks.slack.com/services/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', $settings);
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

    /**
     * Send an error message to Slack.
     *
     * @param      $message The message to send to Slack.
     * @param null $code The error code, if necessary.
     */
    protected function sendSlackError($message, $code = null)
    {
        // define the message/attachment to send
        $attachment = [
            'pretext' => '@here: An error has occurred:',
            'fallback' => $message,
            'author_name' => get_class($this),
            'color' => 'danger',
            'fields' => [
                [
                    'title' => 'Command name:',
                    'value' => (new \ReflectionClass($this))->getShortName(),
                    'short' => true,
                ],
                [
                    'title' => 'Command description:',
                    'value' => $this->getDescription(),
                    'short' => true,
                ], [
                    'title' => 'Error:',
                    'value' => $message,
                    'short' => true,
                ],
            ],
        ];

        // get the current relative directory, and set the link to GitLab
        preg_match('/\/app\/Console\/Commands\/.*$/', __FILE__, $directory);
        $directory = $directory[0];
        if (App::environment('production')) {
            $attachment['author_link'] = 'https://gitlab.com/vatsim-uk/core/blob/production' . $directory;
        } else {
            $attachment['author_link'] = 'https://gitlab.com/vatsim-uk/core/blob/development' . $directory;
        }

        // if an error code has been provided, add it as a field
        if ($code !== null) {
            $attachment['fields'][] = [
                'title' => 'Error code:',
                'value' => $code,
                'short' => true,
            ];
        }

        $this->slack->attach($attachment)->send();
    }

    /**
     * Send a success message to Slack.
     *
     * @param string $message The message to send.
     */
    protected function sendSlackSuccess($message = 'Command has run successfully.')
    {
        $attachment = [
            'fallback' => $message,
            'author_name' => get_class($this),
            'color' => 'good',
            'fields' => [
                [
                    'title' => 'Command name:',
                    'value' => (new \ReflectionClass($this))->getShortName(),
                    'short' => true,
                ],
                [
                    'title' => 'Command description:',
                    'value' => $this->getDescription(),
                    'short' => true,
                ], [
                    'title' => 'Message:',
                    'value' => $message,
                    'short' => true,
                ],
            ],
        ];

        $this->slack->attach($attachment)->send();
    }
}
