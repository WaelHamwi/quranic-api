<?php

return [
    'paths' => ['api/*', '*'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'http://localhost:8081',
        'exp://192.168.100.34:8081',
        'http://10.0.2.2:8000',
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => false,
];