<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WebSocket Server Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for the WebSocket server.
    |
    */

    'apps' => [
        [
            'id' => env('PUSHER_APP_ID'),
            'name' => 'Local WebSockets',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'path' => null,
            'capacity' => null,
            'enable_client_messages' => true,
            'enable_statistics' => true,
        ],
    ],

    'host' => '127.0.0.1', // Ensure this matches your local environment
    'port' => 6001, // Ensure this port is open and not blocked by a firewall

    /*
    |--------------------------------------------------------------------------
    | WebSocket SSL Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the SSL settings for the WebSocket server.
    |
    */

    'ssl' => [
        'local_cert' => null, // Set this if using SSL
        'local_pk' => null,   // Set this if using SSL
        'passphrase' => null, // Set this if using SSL
    ],

    /*
    |--------------------------------------------------------------------------
    | WebSocket Path Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the path settings for the WebSocket server.
    |
    */

    'path' => env('PUSHER_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Capacity Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the capacity settings for the WebSocket server.
    |
    */

    'capacity' => env('PUSHER_CAPACITY', null),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Client Messages Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the client messages settings for the WebSocket server.
    |
    */

    'enable_client_messages' => env('PUSHER_ENABLE_CLIENT_MESSAGES', true),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Statistics Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the statistics settings for the WebSocket server.
    |
    */

    'enable_statistics' => env('PUSHER_ENABLE_STATISTICS', true),

    'statistics' => [
        'interval_in_seconds' => 60,
        'logger' => BeyondCode\LaravelWebSockets\Statistics\Logger\HttpStatisticsLogger::class,
        'http' => [
            'host' => '127.0.0.1',
            'port' => 8000,
        ],
    ],

];
