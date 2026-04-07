<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens;

use Illuminate\Support\ServiceProvider;

final class FilamentSanctumTokensServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->bootPublishes();
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-sanctum-tokens.php',
            'filament-sanctum-tokens'
        );
    }

    private function registerBindings(): void
    {
        $this->app->singleton(
            namespace\Contracts\FilamentSanctumTokensContract::class,
            static function ($app) {
                return new namespace\FilamentSanctumTokens(
                    modelsInclude: config('filament-sanctum-tokens.models.include'),
                    modelsExclude: config('filament-sanctum-tokens.models.exclude'),
                );
            }
        );
        $this->app->alias(
            namespace\Contracts\FilamentSanctumTokensContract::class,
            'filament-sanctum-tokens'
        );
    }

    private function bootPublishes(): void
    {
        $this->publishes([
            __DIR__.'/../config/filament-sanctum-tokens.php' => config_path('filament-sanctum-tokens.php'),
        ], 'filament-sanctum-tokens-config');
    }
}
