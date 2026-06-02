<?php

return [

    'mentoring' => [
        'no_show_delay_minutes' => (int) env('TRAINING_MENTORING_NO_SHOW_DELAY_MINUTES', 5),
        'short_notice_hours' => (int) env('TRAINING_MENTORING_SHORT_NOTICE_HOURS', 24),
    ],

    'discord' => [
        'exam_announce_channel_id' => env('DISCORD_EXAM_ANNOUNCE_CHANNEL_ID', '1086017278988013609'),
        'exam_pilot_role_id' => env('DISCORD_EXAM_PILOT_ROLE_ID', '1086016803047747675'),
        'exam_controller_role_id' => env('DISCORD_EXAM_CONTROLLER_ROLE_ID', '1086016870282432663'),
        'exam_success_channel_id' => env('DISCORD_EXAM_SUCCESS_CHANNEL_ID', '1135654373981180034'),
        'mentoring_announce_channel_id' => env('DISCORD_MENTORING_ANNOUNCE_CHANNEL_ID', '1086017310101344406'),
        'mentoring_pilot_role_id' => env('DISCORD_MENTORING_PILOT_ROLE_ID', '1086016916394606622'),
        'mentoring_controller_role_id' => env('DISCORD_MENTORING_CONTROLLER_ROLE_ID', '1086016985537716256'),
        'vatuk_emoji_name_and_id' => env('DISCORD_VATUK_EMOJI_NAME_AND_ID', 'vuktrail:740917513436790834'),
    ],

];
