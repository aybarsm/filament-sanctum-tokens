<?php

declare(strict_types=1);

return [
    'cache' => [
        'enabled' => app()->isProduction(),
        'store' => env('CACHE_STORE', 'database'),
        'key' => 'filament-sanctum-tokens',
    ],
    'models' => [
        'include' => [
            app_path('Models'),
        ],
        'exclude' => [
            \App\Models\User::class,
        ],
    ],
];
