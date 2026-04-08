<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\Pages;
use Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\SanctumTokenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class ListTokens extends ListRecords
{
    protected static string $resource = SanctumTokenResource::class;

    protected static function createTokenModel(array $data): Model
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
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(static fn (array $data): Model => self::createTokenModel($data)),
        ];
    }
}
