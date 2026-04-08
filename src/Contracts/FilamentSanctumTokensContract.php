<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Contracts;
use Illuminate\Database\Eloquent\Model;

interface FilamentSanctumTokensContract
{
    public static function getFilamentPluginClass(): string;
    public function getDiscoveredModels(): array;
    public static function getSanctumExpiration(): ?float;
    public static function getTokenDefaultExpiresAt(): ?\DateTimeInterface;
}
