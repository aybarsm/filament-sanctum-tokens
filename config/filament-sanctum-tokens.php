<?php

declare(strict_types=1);

return [
    'cache' => [
        'enabled' => app()->isProduction(),
        'store' => env('CACHE_STORE', 'database'),
        'key' => 'filament-sanctum-tokens',
    ],
    'models' => [
        /**
         * Path or class
         */
        'include' => [
            app_path('Models'),
        ],
        'exclude' => [
        ],
    ],
];
