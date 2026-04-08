<?php

declare(strict_types=1);

return [
    'defaults' => [
        'expiration' => [
            'unit' => 'day',
            'value' => 14,
        ],
    ],
    'cache' => [
        'enabled' => app()->isProduction(),
        'store' => env('CACHE_STORE', 'database'),
        'key' => 'filament-sanctum-tokens',
    ],
    'models' => [
        /**
         *
         * Classes or paths
         * (Discovery uses composer's autoload_classmap)
         *
         * Discovered classes must implement the interfaces:
         * - \Illuminate\Database\Eloquent\Model::class
         * - \Illuminate\Contracts\Auth\Authenticatable::class
         * - \Laravel\Sanctum\Contracts\HasApiTokens::class
         *
         */
        'include' => [
            app_path('Models'),
        ],
        'exclude' => [
        ],
    ],
];
