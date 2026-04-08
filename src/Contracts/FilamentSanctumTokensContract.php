<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Contracts;
use Illuminate\Database\Eloquent\Model;

interface FilamentSanctumTokensContract
{
    public static function getTokenModel(): string;
    public static function getTokenModelObject(): Model;
    public function getModelDiscovery(): array;
    public function getDiscoveredModels(): array;
    public static function isClassEligible(string $class): bool;
    public function getCache(): ?array;
    public function getTokenModelSelectFields(): array;
    public static function resolveMorphClass(string $value): ?string;
    public static function resolveMorphType(string $value): ?string;
    public static function getSanctumExpiration(): ?float;
    public static function getTokenDefaultExpiresAt(): ?\DateTimeInterface;

}
