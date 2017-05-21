<?php

namespace App\Console\Commands;

use App;
use Slack;
use Illuminate\Console\Command as BaseCommand;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends BaseCommand
{
    /**
     * Log a string to STDOUT.
     * If STDOUT is piped/redirected, styling is removed.
     *
     * @param string $message The string to output.
     * @param null   $style   The styling to output.
     * @param bool   $newline If a new line should be returned at the end.
     */
    protected function log($message, $style = null, $newline = true)
    {
        // keep styling if output is not piped, or if we can't tell
        if (function_exists('posix_isatty')) {
            $style = posix_isatty(STDOUT) ? $style : null;
        } else {
            if (App::environment('production')) {
                $this->sendSlackError('posix_isatty is not available in production - install POSIX extension (php-common)');
            }
        }

        // add style tags to the output string
        $styled = $style ? "<$style>$message</$style>" : $message;

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
     * Send a direct message to a Slack user.
     *
     * @param Account|string $to    Either the local Account or the SlackUserID to send a message to.
     * @param string         $message The message to send to the user
     */
    protected function sendSlackMessagePlain($to, $message, $from = null)
    {
        if (is_object($to) && $to->exists) {
            $to = $to->slack_id;
        }

        $slack = $this->slack()->to($to);

        if ($from != null) {
            $slack = $slack->from($from);
        }

        $slack->send($message);
    }

    protected function sendSlackMessageFormatted($to, $pretext, $message, $colour = 'danger', $fields = [], $from = null)
    {
        $attachment = [
            'pretext' => '@here: '.$pretext,
            'fallback' => $message,
            'author_name' => 'VATSIM UK Slack Bot',
            'color' => $colour,
        ];

        $attachment['author_link'] = $this->getAuthorLink();

        foreach ($fields as $index => $message) {
            $attachment['fields'][] = [
                'title' => $index,
                'value' => $message,
                'short' => true,
            ];
        }

        $slack = $this->slack()->to($to);

        if ($from != null) {
            $slack = $slack->from($from);
        }

        $slack->attach($attachment)->send();
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
                ],
                [
                    'title' => 'Error:',
                    'value' => $message,
                    'short' => true,
                ],
            ],
        ];

        $attachment['author_link'] = $this->getAuthorLink();

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
        if ($this->getOutput()->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return false;
        }
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
                ],
                [
                    'title' => 'Message:',
                    'value' => $message,
                    'short' => true,
                ],
            ],
        ];

        $attachment['author_link'] = $this->getAuthorLink();

        foreach ($fields as $index => $message) {
            $attachment['fields'][] = [
                'title' => $index,
                'value' => $message,
                'short' => true,
            ];
        }

        $this->slack()->attach($attachment)->send();
    }

    protected function getAuthorLink()
    {
        // get the current relative directory, and set the link to GitLab
        preg_match('/\/app\/Console\/Commands\/.*$/', __FILE__, $directory);
        $directory = array_get($directory, 0, '');
        if (App::environment('production')) {
            return 'https://gitlab.com/vatsim-uk/core/blob/production'.$directory;
        } else {
            return 'https://gitlab.com/vatsim-uk/core/blob/development'.$directory;
        }
    }
}
