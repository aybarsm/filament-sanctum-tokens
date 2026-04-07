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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource as FilamentResource;
final class SanctumTokenResource extends FilamentResource
{
    protected static ?string $label = 'Sanctum Token';
    protected static ?string $modelLabel = 'Sanctum Token';
    protected static ?string $pluralLabel = 'Sanctum Tokens';
    protected static ?string $pluralModelLabel = 'Sanctum Tokens';
    protected static ?string $recordTitleAttribute = 'Sanctum Token';
    protected static string | \UnitEnum | null $navigationGroup = 'Auth';
    protected static ?string $slug = 'auth/sanctum/tokens';

    public static function getModel(): string
    {
        return Facade::getTokenModel();
    }

    public static function getPages(): array
    {
        return [
            'index' => namespace\Pages\ListTokens::route('/'),
        ];
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

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                static fn (Builder $query) => $query->select(Facade::getTokenModelSelectFields())
            )
//            ->defaultSort('opened_at', 'desc')
            ->columns([
                TextColumn::make('ID')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
//                EditAction::make(),
//                DeleteAction::make(),
            ])
            ->toolbarActions([
//                BulkActionGroup::make([
//                    DeleteBulkAction::make(),
//                ]),
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
