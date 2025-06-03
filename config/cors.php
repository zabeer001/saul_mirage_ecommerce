<?php

return [
 
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
 
    'allowed_methods' => ['*'],
 
    // Allow only your frontend domain — NOT '*'
    'allowed_origins' => ['https://williamcrosb344.vercel.app'],
 
    'allowed_origins_patterns' => [],
 
    'allowed_headers' => ['*'],
 
    'exposed_headers' => [],
 
    'max_age' => 0,
 
    // Must be true when using withCredentials
    'supports_credentials' => true,
 
];