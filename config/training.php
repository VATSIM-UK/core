<?php

return [

    'discord' => [
        'exam_announce_channel_id' => env('DISCORD_EXAM_ANNOUNCE_CHANNEL_ID'),
        'exam_announce_role_id' => env('DISCORD_EXAM_ANNOUNCE_ROLE_ID'),
        'exam_announce_mention_role' => env('DISCORD_EXAM_ANNOUNCE_MENTION_ROLE', true)
    ],

];
