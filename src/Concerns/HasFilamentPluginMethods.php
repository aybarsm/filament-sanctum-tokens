<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Concerns;

use Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\SanctumTokenResource;
use Filament\Panel;
use Aybarsm\Filament\SanctumTokens\Contracts\FilamentSanctumTokensPluginContract as PluginContract;
trait HasFilamentPluginMethods
{
    public function getId(): string
    {
        return 'filament-sanctum-tokens';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            SanctumTokenResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public static function make(): self
    {
        return app(PluginContract::class);
    }

    public static function get(): self
    {
        /** @var static $plugin */
        $plugin = filament(app(PluginContract::class)->getId());

        return $plugin;
    }
}
