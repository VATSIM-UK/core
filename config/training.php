<?php

return [

    'mentoring' => [
        'no_show_delay_minutes' => (int) env('TRAINING_MENTORING_NO_SHOW_DELAY_MINUTES', 5),
        'short_notice_hours' => (int) env('TRAINING_MENTORING_SHORT_NOTICE_HOURS', 24),
    ],

    'availability_warning_days' => [
        'atc' => (int) env('TRAINING_AVAILABILITY_WARNING_DAYS_ATC', 5),
        'pilot' => (int) env('TRAINING_AVAILABILITY_WARNING_DAYS_PILOT', 7),
    ],

    'pilot' => [
        'handbook_url' => env(
            'TRAINING_PILOT_HANDBOOK_URL',
            'https://drive.google.com/file/d/1llKkmgdf1srYxjTkMhd8m4u-lJDxls_6/view?usp=drive_link'
        ),
        // Policy is published within the handbook until a separate policy page exists.
        'policy_url' => env(
            'TRAINING_PILOT_POLICY_URL',
            'https://drive.google.com/file/d/1llKkmgdf1srYxjTkMhd8m4u-lJDxls_6/view?usp=drive_link'
        ),
        'helpdesk_url' => env('TRAINING_PILOT_HELPDESK_URL', 'https://helpdesk.vatsim.uk'),
        'waiting_lists_url' => env('TRAINING_PILOT_WAITING_LISTS_URL', 'https://www.vatsim.uk/mship/waiting-lists'),
        'cts_url' => env('TRAINING_PILOT_CTS_URL', 'https://cts.vatsim.uk'),
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
        'pilot_training_team_channel_id' => env('DISCORD_PILOT_TRAINING_TEAM_CHANNEL_ID', '705818400462602283'),
    ],

];
