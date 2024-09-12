<?php

return [
    'allow_extensions' => [
        'jpg', 'jpeg', 'png', 'bmp', 'gif', 'ico', 'svg', 'webp',
    ],
    /*
    |--------------------------------------------------------------------------
    | Mail client configuration
    |--------------------------------------------------------------------------
    |
    */
    'sync' => [
        /*
        |--------------------------------------------------------------------------
        | Sync mail client interval definition in cron style
        |--------------------------------------------------------------------------
        |
        | By default the mail client synchronizer, sync emails every 3 minutes, the interval can be defined below.
        */
        'interval' => env('MAIL_CLIENT_SYNC_INTERVAL', '*/3 * * * *'),
    ],
];
