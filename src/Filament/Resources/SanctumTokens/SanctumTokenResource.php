<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens;
use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource as FilamentResource;
use Filament\Schemas\Schema as FilamentSchema;
use Laravel\Sanctum\Sanctum;

final class SanctumTokenResource extends FilamentResource
{
    protected static ?string $label = 'Sanctum Token';
    protected static ?string $modelLabel = 'Sanctum Token';
    protected static ?string $pluralLabel = 'Sanctum Tokens';
    protected static ?string $pluralModelLabel = 'Sanctum Tokens';
    protected static ?string $recordTitleAttribute = 'Sanctum Token';
    protected static string | \UnitEnum | null $navigationGroup = 'Auth';
    protected static ?string $slug = 'auth/sanctum/tokens';
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedKey;

    public static function getModel(): string
    {
        return Sanctum::personalAccessTokenModel();
    }

    public static function form(FilamentSchema $schema): FilamentSchema
    {
        return namespace\Schemas\SanctumTokenForm::configure($schema);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return namespace\Tables\SanctumTokensTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => namespace\Pages\ListTokens::route('/'),
        ];
    }
}
