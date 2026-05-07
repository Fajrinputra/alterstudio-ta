<?php

return [
    'open_time' => env('STUDIO_OPEN_TIME', '11:00'),
    'close_time' => env('STUDIO_CLOSE_TIME', '22:00'),
    'slot_interval_minutes' => (int) env('STUDIO_SLOT_INTERVAL_MINUTES', 15),
    'booking_buffer_minutes' => (int) env('STUDIO_BOOKING_BUFFER_MINUTES', 15),
    'closed_weekdays' => [0, 6],
];
