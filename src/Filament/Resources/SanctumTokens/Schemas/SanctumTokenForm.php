<?php

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\View\FormsIconAlias;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Field as FilamentField;
final class SanctumTokenForm
{
    public static function getSanctumExpiration(): ?float
    {
        $expiration = (float) config('sanctum.expiration');
        return $expiration > 0 ? $expiration : null;
    }
    public static function getTokenDefaultExpiresAt(): ?\DateTimeInterface
    {
        $expiration = self::getSanctumExpiration();
        return $expiration ? Carbon::now()->addMinutes($expiration) : null;
    }
    protected static function getTokenableTypes(): array
    {
        return array_map(
            static function (string $class) {
                $label = Relation::getMorphedModel($class) ?? $class;
                $titleAttr = $class::getModel()->getKeyName();
                return Type::make($class)->label($label)->titleAttribute($titleAttr);
            },
            Facade::getDiscoveredModels(),
        );
    }

    protected static function getExpiresAtActions(Schema $schema): array
    {
        $ret = [];

        if ($schema->getOperation() === 'create' && self::getSanctumExpiration()){
            $ret[] = Action::make('expires_at::default')
                ->tooltip('Refresh Default Expires At')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->action(static fn ($set) => $set('expires_at', self::getTokenDefaultExpiresAt()))
                ->visible(static fn ($state) => blank($state));
        }

        if (in_array($schema->getOperation(), ['edit', 'create'])){
            $ret[] = Action::make('expires_at::clear')
                ->tooltip('Clear')
                ->icon('heroicon-m-x-mark')
                ->color('gray')
                ->action(static fn ($set) => $set('expires_at', null))
                ->visible(static fn ($state) => filled($state));
        }

        return $ret;
    }

    protected static function getAbilitiesActions(Schema $schema): array
    {
        $ret = [];
        if (in_array($schema->getOperation(), ['edit', 'create'])) {
            $ret[] = Action::make('abilities::clear')
                ->label('Remove All Abilities')
                ->tooltip('Clear')
                ->icon('heroicon-m-x-mark')
                ->color('gray')
                ->requiresConfirmation()
                ->action(static fn($set) => $set('abilities', null))
                ->visible(static fn($state) => filled($state));
        }

        return $ret;
    }

    protected static function getTokenValue(Schema $schema): string
    {
        $record = $schema->getRecord();

        if (method_exists($record, 'getPlainTextToken')) {
            $ret = $schema->evaluate(\Closure::fromCallable([$record, 'getPlainTextToken']));
        } else {
            $record->makeVisible('token');
            $ret = "{$record->getKey()}|{$record->token}";
            $record->makeHidden('token');
        }

        return $ret;
    }

    protected static function makeViewTokenInput(Schema $schema): TextInput
    {
        $ret = TextInput::make('plainTextToken')
            ->label('Token')
            ->meta('valueHidden', str_repeat('*', 50))
            ->meta('valueRevealed', self::getTokenValue($schema))
            ->columnSpanFull()
            ->live()
            ->formatStateUsing(static fn (?string $state, TextInput $component): string => $state === null ? $component->getMeta('valueHidden') : $state)
            ->password(static fn (?string $state, TextInput $component) => $state === $component->getMeta('valueHidden'))
            ->copyable(static fn (?string $state, TextInput $component): bool => $state === $component->getMeta('valueRevealed'));

        $ret->suffixActions([
            Action::make('token::toggle')
                ->tooltip('Reveal Token')
                ->icon('heroicon-m-eye')
                ->color('gray')
                ->action(static function (?string $state, TextInput $component, Action $action, $set) {
                    $valueHidden = $component->getMeta('valueHidden');
                    $isHidden = $state === $valueHidden;
                    if ($isHidden) {
                        $set('plainTextToken', $component->getMeta('valueRevealed'));
                        $action->tooltip('Hide Token')->icon('heroicon-m-eye-slash');
                    }else {
                        $set('plainTextToken', $valueHidden);
                        $action->tooltip('Reveal Token')->icon('heroicon-m-eye');
                    }
                })
        ]);

        return $ret;
    }

    protected static function getSchemaComponents(Schema $schema): array
    {
        $ret = [];

        if ($schema->getOperation() === 'view'){
            $ret[] = self::makeViewTokenInput($schema);
        }

        if (in_array($schema->getOperation(), ['view', 'edit'])){
            $ret[] = TextInput::make('id')
                ->label('ID')
                ->columnSpan(['default' => 'half'])
                ->disabled()
                ->dehydrated(false);

            $ret[] = DateTimePicker::make('last_used_at')
                ->label('Last Used At')
                ->native(false)
                ->placeholder('Never')
                ->columnSpan(['default' => 'half'])
                ->disabled()
                ->visible(in_array($schema->getOperation(), ['view', 'edit']))
                ->nullable()
                ->dehydrated(false)
                ->hint(static fn ($state, DateTimePicker $component) => filled($state) ? "Timezone: {$component->getTimezone()}" : null)
                ->displayFormat('Y-m-d H:i:s');
        }

        $ret[] = TextInput::make('name')
            ->label('Name')
            ->required()
            ->columnSpan(['default' => 'half']);

        $ret[] = DateTimePicker::make('expires_at')
            ->label('Expires At')
            ->native(false)
            ->default($schema->getOperation() === 'create' ? self::getTokenDefaultExpiresAt() : null)
            ->format('Y-m-d H:i:s P')
            ->displayFormat('Y-m-d H:i:s')
            ->nullable()
            ->hint(static fn ($state, DateTimePicker $component) => filled($state) ? "Timezone: {$component->getTimezone()}" : null)
            ->placeholder('Never')
            ->suffixActions(self::getExpiresAtActions($schema), true)
            ->live()
            ->columnSpan(['default' => 'half']);

        if (in_array($schema->getOperation(), ['edit', 'create'])) {
            $ret[] = TagsInput::make('abilities')
                ->label('Abilities')
                ->default(['*'])
                ->splitKeys(['Enter', ' ', ','])
                ->suffixActions(self::getAbilitiesActions($schema), true)
                ->placeholder('Add New Ability')
                ->live()
                ->columnSpanFull();
        }else {
            $ret[] = TextArea::make('abilities')
                ->label('Abilities')
                ->columnSpanFull();
        }

        $ret[] = MorphToSelect::make('tokenable')
            ->label('Model')
            ->types(self::getTokenableTypes())
            ->required()
            ->columns(['md' => 2, 'default' => 1])
            ->columnSpanFull();

        return $ret;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getSchemaComponents($schema));
    }
}
