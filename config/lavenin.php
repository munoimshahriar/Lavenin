<?php

return [
    // Used only by `php artisan db:seed` to create the first owner account.
    'owner' => [
        'name' => env('OWNER_NAME', 'Lavenin Owner'),
        'email' => env('OWNER_EMAIL', 'owner@lavenin.com'),
        'password' => env('OWNER_PASSWORD'),
    ],
];
