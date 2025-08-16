<?php

return [
    'api_key' => env('MAILEROO_API_KEY', ''),
    'timeout' => (int)env('MAILEROO_TIMEOUT', 30),
    'tracking' => filter_var(env('MAILEROO_TRACKING', null), FILTER_VALIDATE_BOOLEAN),
    'tags' => json_decode(env('MAILEROO_TAGS', '{}'), true) ? : [],
];