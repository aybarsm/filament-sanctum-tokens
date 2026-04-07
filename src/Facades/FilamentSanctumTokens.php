<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Facades;
use Aybarsm\Filament\SanctumTokens\Contracts\FilamentSanctumTokensContract;
use Illuminate\Support\Facades\Facade;
final class FilamentSanctumTokens extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FilamentSanctumTokensContract::class;
    }
}
