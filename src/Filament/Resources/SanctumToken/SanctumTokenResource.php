<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumToken;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource as FilamentResource;
use Illuminate\Database\Eloquent\Model;
final class SanctumTokenResource extends FilamentResource
{
    protected static ?string $label = 'Sanctum Token';

    protected static ?string $modelLabel = 'Personal Access Token';
    protected static ?string $pluralLabel = 'Sanctum Tokens';
    protected static ?string $pluralModelLabel = 'Personal Access Tokens';
    protected static ?string $recordTitleAttribute = 'Personal Access Token';
    protected static string | \UnitEnum | null $navigationGroup = 'Auth';
//    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'auth/sanctum/tokens';

    public static function getModel(): string
    {
        return \Laravel\Sanctum\Sanctum::personalAccessTokenModel();
    }

    public static function getModelObject(): Model
    {
        return self::getModel()::getModel();
    }

//    public static function form(Schema $schema): Schema
//    {
//        return $schema
//            ->components([
//                Select::make('symbol_id')
//                    ->relationship('symbol', 'name')
//                    ->required(),
//                TextInput::make('period')
//                    ->required(),
//                DateTimePicker::make('opened_at')
//                    ->required(),
//                TextInput::make('open')
//                    ->required()
//                    ->numeric(),
//                TextInput::make('high')
//                    ->required()
//                    ->numeric(),
//                TextInput::make('low')
//                    ->required()
//                    ->numeric(),
//                TextInput::make('close')
//                    ->required()
//                    ->numeric(),
//                TextInput::make('volume')
//                    ->numeric(),
//                TextInput::make('data'),
//            ]);
//    }
//
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                static fn (Builder $query) => $query->select(['id', 'symbol_id', 'period', 'opened_at'])
            )
            ->defaultSort('opened_at', 'desc')
            ->recordTitleAttribute('Candle')
            ->columns([
                TextColumn::make('symbol.name')
                    ->searchable(),
                TextColumn::make('period')
                    ->searchable(),
                TextColumn::make('opened_at')
                    ->dateTime('Y-m-d H:i:s', 'UTC')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

//    public static function getRelations(): array
//    {
//        return self::getModel()::getModel()->getRelations();
//    }
//    public static function getRelations(): array
//    {
//        return
//    }
}
