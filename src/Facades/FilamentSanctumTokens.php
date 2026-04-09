<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Facades;
use Aybarsm\Filament\SanctumTokens\Contracts\FilamentSanctumTokensPluginContract;
use Illuminate\Support\Facades\Facade;

/**
 * @see FilamentSanctumTokensPluginContract
 */
final class FilamentSanctumTokens extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FilamentSanctumTokensPluginContract::class;
    }
}
