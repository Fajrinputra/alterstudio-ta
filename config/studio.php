<?php

return [
    'open_time' => env('STUDIO_OPEN_TIME', '11:00'),
    'close_time' => env('STUDIO_CLOSE_TIME', '22:00'),
    'slot_interval_minutes' => (int) env('STUDIO_SLOT_INTERVAL_MINUTES', 60),
    'closed_weekdays' => [0, 6],
    'holidays' => [
        // Tambahkan tanggal libur studio dengan format Y-m-d.
        // Contoh: '2026-01-01',
    ],
];
