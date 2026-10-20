<?php

return [
    'max_advance_bookings' => (int) env('BOOKINGS_MAX_ADVANCE_BOOKINGS', 6),
    'max_advance_days' => (int) env('BOOKINGS_MAX_ADVANCE_DAYS', 90),
    'min_advance_hours' => (int) env('BOOKINGS_MIN_ADVANCE_HOURS', 2),

    // Window covered by the per-member calendar feed.
    'feed_past_days' => (int) env('BOOKINGS_FEED_PAST_DAYS', 30),
    'feed_future_days' => (int) env('BOOKINGS_FEED_FUTURE_DAYS', 180),

    'gatwick' => [
        'max' => (int) env('BOOKINGS_GATWICK_MAX', 2),
        'position_patterns' => [
            'EGKK_%GND%',
            'EGKK_%DEL%',
        ],
    ],
];
