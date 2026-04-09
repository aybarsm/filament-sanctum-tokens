<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Contracts;
use Filament\Contracts\Plugin as FilamentPluginContract;
use Illuminate\Database\Eloquent\Model;

interface FilamentSanctumTokensPluginContract extends FilamentPluginContract
{
    public function getDiscoveredModels(): array;
}
