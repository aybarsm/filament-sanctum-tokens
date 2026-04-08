<?php

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
final class SanctumTokenForm
{
    protected static function getTokenableTypes(): array
    {
        return array_map(
            static function (string $class) {
                $label = str($class)->trim()->trim('\\')->chopEnd('::class')->value();
                $titleAttr = $class::getModel()->getKeyName();
                return Type::make($class)->label($label)->titleAttribute($titleAttr);
            },
            Facade::getDiscoveredModels(),
        );
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->columnSpanFull(),
                MorphToSelect::make('tokenable')
                    ->label('Model')
                    ->types(self::getTokenableTypes())
                    ->required()
                    ->columns([
                        'md' => 2,
                        'default' => 1,
                    ])
                    ->columnSpanFull(),
                Textarea::make('abilities')
                    ->label('Abilities')
                    ->columnSpanFull(),
                DateTimePicker::make('expires_at')
                    ->label('Expires At'),
            ]);
    }
}
