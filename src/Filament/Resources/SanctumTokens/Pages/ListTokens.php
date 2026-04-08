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


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(static fn (array $data): Model => filament('')),
        ];
    }
}
