<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Concerns;

use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
use Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\SanctumTokenResource;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
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
