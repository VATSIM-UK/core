<?php

namespace App\Libraries;

use App;
use Vluzrmos\SlackApi\Facades\SlackChat;

class Slack
{
    public static function generateAttachmentForMessage($message, $fields = [], $actions = [], $author = null, $color = '#428bca')
    {
        $attachment = [
            'fallback' => $message,
            'author_name' => $author,
            'color' => $color,
            'text' => $message,
            'actions' => $actions,
            'fields' => [],
        ];

        foreach ($fields as $index => $message) {
            $attachment['fields'][] = [
                'title' => $index,
                'value' => $message,
                'short' => true,
            ];
        }

        return $attachment;
    }

    public static function sendToWebServices($message, $attachment = null, $username = null)
    {
        return self::send("#web_alerts", $message, $attachment, $username);
    }

    /**
     * @param mixed $channel Can be either a Slack ID ("UBE27JE8"), An Account or Channel that the Bot is in ("#web_alerts")
     * @param string $message  Message
     * @param null $attachment Formatted Attachment https://api.slack.com/docs/message-attachments#attachment_structure
     * @param string $as Displayed Bot Name
     * @return mixed
     */
    public static function send($channel, $message, $attachment = null, $username = null)
    {
        if (is_object($channel) && get_class($channel) == App\Models\Mship\Account::class && $channel->slack_id) {
            $channel = $channel->slack_id;
        }

        return SlackChat::message($channel, $message, [
            'attachments' => $attachment,
            'username' => $username
        ]);
    }
}
