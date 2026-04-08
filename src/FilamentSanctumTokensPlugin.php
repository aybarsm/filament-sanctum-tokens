<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens;
use Aybarsm\Filament\SanctumTokens\Contracts\FilamentSanctumTokensPluginContract;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

final class FilamentSanctumTokensPlugin implements FilamentSanctumTokensPluginContract
{
    public function getId(): string
    {
        return 'filament-sanctum-tokens';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            namespace\Filament\Resources\SanctumTokens\SanctumTokenResource::class,
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

    public static function createTokenModel(array $data): Model
    {
        $modelId = $data['tokenable_id'];
        $model = $data['tokenable_type'];
        $model = Relation::getMorphedModel($model) ?? $model;
        $model = $model::findOrFail($modelId, $model::getModel()->getKeyName());

        $args = [
            'name' => $data['name'],
            'abilities' => $data['abilities'] ?? [],
            'expiresAt' => $data['expires_at'] ?? null,
        ];

        if (isset($args['expiresAt'])) {
            $args['expiresAt'] = Carbon::parse($args['expiresAt']);
        }
        return $model->createToken(...$args)->accessToken;
    }
}
