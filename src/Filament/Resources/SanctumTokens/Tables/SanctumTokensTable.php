<?php

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\Tables;

use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;

class SanctumTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('tokenable_type')
                    ->label('Model')
                    ->formatStateUsing(static fn (string $state) => Facade::resolveMorphClass($state))
                    ->searchable(),
                TextColumn::make('tokenable_id')
                    ->label('Model ID')
                    ->sortable(),
                TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->dateTime(format: 'Y-m-d\TH:i:s\Z', timezone: 'UTC')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime(format: 'Y-m-d\TH:i:s\Z', timezone: 'UTC')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime(format: 'Y-m-d\TH:i:s\Z', timezone: 'UTC')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime(format: 'Y-m-d\TH:i:s\Z', timezone: 'UTC')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->tooltip('View Token'),
                EditAction::make()->iconButton()->tooltip('Edit'),
                DeleteAction::make()->iconButton()->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
