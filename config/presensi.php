<?php

return [
    'sekolah_lat' => (float) env('PRESENSI_SEKOLAH_LAT', -7.018907),
    'sekolah_lng' => (float) env('PRESENSI_SEKOLAH_LNG', 110.359350),
    'radius_m' => (int) env('PRESENSI_RADIUS_M', 20),
    'jam_masuk' => env('PRESENSI_JAM_MASUK', '07:00:00'),
];
