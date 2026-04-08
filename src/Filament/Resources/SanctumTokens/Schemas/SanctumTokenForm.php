<?php

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
use Illuminate\Database\Eloquent\Relations\Relation;

final class SanctumTokenForm
{
    protected static function getTokenableTypes(): array
    {
        return array_map(
            static function (string $class) {
                $label = Relation::getMorphedModel($class);
                $titleAttr = $class::getModel()->getKeyName();
                return Type::make($class)->label($label)->titleAttribute($titleAttr);
            },
            Facade::getDiscoveredModels(),
        );
    }

    protected static function getExpiresAtActions(): array
    {
        $ret = [];
        if (Facade::getSanctumExpiration()){
            $ret[] = Action::make('expires_at::default')
                ->tooltip('Refresh Default Expires At')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->action(static fn ($set) => $set('expires_at', Facade::getTokenDefaultExpiresAt()))
                ->visible(static fn ($state, string $operation) => $operation === 'create');
        }

        $ret[] = Action::make('expires_at::clear')
            ->tooltip('Clear')
            ->icon('heroicon-m-x-mark')
            ->color('gray')
            ->action(static fn ($set) => $set('expires_at', null))
            ->visible(static fn ($state) => filled($state));

        return $ret;
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('ID')
                    ->columnSpan(['default' => 'half'])
                    ->disabled()
                    ->visible(fn (string $operation) => $operation === 'edit')
                    ->dehydrated(false),
                DateTimePicker::make('last_used_at')
                    ->label('Last Used At')
                    ->native(false)
                    ->placeholder('Never')
                    ->columnSpan(['default' => 'half'])
                    ->disabled()
                    ->visible(fn (string $operation) => $operation === 'edit')
                    ->nullable()
                    ->dehydrated(false)
                    ->hint(static fn ($state, DateTimePicker $component) => filled($state) ? "Timezone: {$component->getTimezone()}" : null)
                    ->displayFormat('Y-m-d H:i:s'),
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->columnSpan(['default' => 'half']),
                DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->native(false)
                    ->default(static fn ($operation) => $operation === 'create' ? Facade::getTokenDefaultExpiresAt() : null)
                    ->displayFormat('Y-m-d H:i:s')
                    ->nullable()
                    ->live()
                    ->hint(static fn ($state, DateTimePicker $component) => filled($state) ? "Timezone: {$component->getTimezone()}" : null)
                    ->placeholder('Never')
                    ->suffixActions(self::getExpiresAtActions(), true)
                    ->columnSpan(['default' => 'half']),
                Textarea::make('abilities')
                    ->label('Abilities')
                    ->columnSpanFull(),
                MorphToSelect::make('tokenable')
                    ->label('Model')
                    ->types(self::getTokenableTypes())
                    ->required()
                    ->columns(['md' => 2, 'default' => 1])
                    ->columnSpanFull(),
            ]);
    }
}
