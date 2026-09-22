<?php

return [
    'name' => env('BRAND_NAME', 'Church of Uganda Youth Platform'),
    'short_name' => env('BRAND_SHORT_NAME', 'COU Youth Platform'),
    'tagline' => env('BRAND_TAGLINE', 'Connecting Young People. Growing Disciples. Transforming Nations.'),
    'primary_color' => env('BRAND_PRIMARY_COLOR', '#4B2E83'),
    'secondary_color' => env('BRAND_SECONDARY_COLOR', '#204F78'),
    'support_email' => env('BRAND_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS')),
    'logo_path' => env('BRAND_LOGO_PATH'),
];
