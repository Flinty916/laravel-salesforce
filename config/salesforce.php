<?php

return [

    'client_id' => env('SALESFORCE_CLIENT_ID'),
    'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
    'username' => env('SALESFORCE_USERNAME'),
    'password' => env('SALESFORCE_PASSWORD'),
    'login_url' => env('SALESFORCE_LOGIN_URL', 'https://login.salesforce.com'),
    'api_version' => env('SALESFORCE_API_VERSION', '58.0'),
    'cache' => [
        'enabled' => true,
        'key_prefix' => 'salesforce_',
        'token_ttl' => 60, // 1 minute
    ],
];
