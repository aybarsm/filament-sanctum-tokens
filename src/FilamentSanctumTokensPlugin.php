<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens;
use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
use Aybarsm\Filament\SanctumTokens\Contracts\FilamentSanctumTokensPluginContract;
use Filament\Panel;

final class FilamentSanctumTokensPlugin implements FilamentSanctumTokensPluginContract
{
    public function getId(): string
    {
        return 'filament-sanctum-tokens';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            Facade::getFilamentResourceClass(),
        ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public static function make(): self
    {
        return app(namespace\Contracts\FilamentSanctumTokensPluginContract::class);
    }

    public static function get(): self
    {
        /** @var static $plugin */
        $plugin = filament(app(namespace\Contracts\FilamentSanctumTokensPluginContract::class)->getId());

        return $plugin;
    }
}
