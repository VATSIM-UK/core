<?php

namespace App\Libraries;

use App;
use Slack as SlackInterface;

class Slack
{
    /**
     * Return a Slack configuration for message sending.
     *
     * Method must be called for each message sent, to avoid message stacking/overlap.
     *
     * @return mixed
     */
    protected static function slack()
    {
        if (App::environment('production')) {
            $channel = 'wslogging';
        } else {
            $channel = 'wslogging_dev';
        }

        return SlackInterface::setUsername('Core Notifications')->to($channel);
    }

    protected static function send($author, $message = '', $fields = [], $color = 'good')
    {
        // define the message/attachment to send
        $attachment = [
            'fallback' => $message,
            'author_name' => $author,
            'color' => $color,
            'fields' => [
                [
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

        self::slack()->attach($attachment)->send();
    }

    /**
     * Send an error message to Slack.
     *
     * @param        $author
     * @param string $message The message to send to Slack.
     * @param array $fields
     */
    public static function sendError($author, $message = '', $fields = [])
    {
        self::send($author, $message, $fields, 'danger');
    }

    /**
     * Send a success message to Slack.
     *
     * @param        $author
     * @param string $message The message to send.
     * @param array $fields
     */
    public static function sendSuccess($author, $message = '', $fields = [])
    {
        self::send($author, $message, $fields, 'good');
    }

    /**
     * Send a neutral message to Slack.
     *
     * @param        $author
     * @param string $message
     * @param array $fields
     */
    public static function sendMessage($author, $message = '', $fields = [])
    {
        self::send($author, $message, $fields, '#428bca');
    }
}
