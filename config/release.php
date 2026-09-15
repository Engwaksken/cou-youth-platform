<?php

return [
    'version' => env('APP_RELEASE_VERSION', '0.8.0'),
    'build' => env('APP_RELEASE_BUILD', '8'),
    'channel' => env('APP_RELEASE_CHANNEL', 'production'),
    'released_at' => env('APP_RELEASED_AT'),
    'minimum_mobile_version' => env('MINIMUM_MOBILE_VERSION', '0.1.0'),
    'maintenance_message' => env('MOBILE_MAINTENANCE_MESSAGE'),
];
