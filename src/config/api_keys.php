<?php

return [
    // default length of generated api keys
    'token_length' => env('API_KEY_LENGTH', 64),
    // enable caching of token lookups
    'cache_enabled' => env('API_KEY_CACHE_ENABLED', true),
    // time to cache token lookup results
    'cache_ttl_seconds' => env('API_KEY_CACHE_TTL', 60),
];
