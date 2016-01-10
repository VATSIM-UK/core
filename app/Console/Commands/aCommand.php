<?php

namespace App\Console\Commands;

use App;
use Slack;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class aCommand extends Command {
    /**
     * Log a string to STDOUT.
     * If STDOUT is piped/redirected, styling is removed.
     *
     * @param string     $string  The string to output.
     * @param null       $style   The styling to output.
     * @param bool|false $newline If a new line should be returned at the end.
     */
    protected function log($string, $style = null, $newline = true)
    {
        // keep styling if output is not piped, or if we can't tell
        if (function_exists('posix_isatty')) {
            $style = posix_isatty(STDOUT) ? $style : null;
        }

        // add style tags to the output string
        $styled = $style ? "<$style>$string</$style>" : $string;

        // write the output
        $this->output->write($styled, $newline, OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * Return a Slack configuration for message sending.
     *
     * Method must be called for each message sent, to avoid message stacking/overlap.
     *
     * @return mixed
     */
    protected function slack()
    {
        if (App::environment('production')) {
            $channel = 'wslogging';
        } else {
            $channel = 'wslogging_dev';
        }

        return Slack::setUsername('Cron Notifications')->to($channel);
    }

    /**
     * Send an error message to Slack.
     *
     * @param string $message The message to send to Slack.
     * @param array  $fields
     */
    protected function sendSlackError($message, $fields = [])
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

        foreach ($fields as $index => $message) {
            $attachment['fields'][] = [
                'title' => $index,
                'value' => $message,
                'short' => true,
            ];
        }

        $this->slack()->attach($attachment)->send();
    }

    /**
     * Send a success message to Slack.
     *
     * @param string $message The message to send.
     * @param array  $fields
     */
    protected function sendSlackSuccess($message = 'Command has run successfully.', $fields = [])
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

        foreach ($fields as $index => $message) {
            $attachment['fields'][] = [
                'title' => $index,
                'value' => $message,
                'short' => true,
            ];
        }

        $this->slack()->attach($attachment)->send();
    }
}
