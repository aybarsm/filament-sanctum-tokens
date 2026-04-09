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
        $this->app->singletonIf(
            namespace\Contracts\FilamentSanctumTokensPluginContract::class,
            namespace\FilamentSanctumTokensPlugin::class,
        );
        $this->app->alias(
            namespace\Contracts\FilamentSanctumTokensPluginContract::class,
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
