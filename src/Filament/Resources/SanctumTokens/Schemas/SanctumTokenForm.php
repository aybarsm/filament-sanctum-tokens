<?php

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SanctumTokenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tokenable_type')
                    ->label('Model')
                    ->required(),
                TextInput::make('tokenable_id')
                    ->label('Model Record')
                    ->required(),
                Textarea::make('name')
                    ->label('Name')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('abilities')
                    ->label('Abilities')
                    ->columnSpanFull(),
                DateTimePicker::make('expires_at')
                    ->label('Expires At'),
            ]);
    }
}
