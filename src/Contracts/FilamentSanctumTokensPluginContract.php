<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Contracts;
use Filament\Contracts\Plugin as FilamentPluginContract;
interface FilamentSanctumTokensPluginContract extends FilamentPluginContract
{
    public function getDiscoveredModels(): array;
    public static function getSanctumExpiration(): ?float;
    public static function getTokenDefaultExpiresAt(): ?\DateTimeInterface;
}
