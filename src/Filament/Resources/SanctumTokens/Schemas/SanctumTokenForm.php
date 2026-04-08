<?php

namespace Aybarsm\Filament\SanctumTokens\Filament\Resources\SanctumTokens\Schemas;

use Filament\Actions\Action;
use Filament\Actions\Events\ActionCalled;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextInput\Actions\CopyAction;
use Filament\Forms\Components\TextInput\Actions\HidePasswordAction;
use Filament\Forms\View\FormsIconAlias;
use Filament\Schemas\Schema;
use Aybarsm\Filament\SanctumTokens\Facades\FilamentSanctumTokens as Facade;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Filament\Forms\Components\TextInput\Actions\ShowPasswordAction;
final class SanctumTokenForm
{
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

        if ($schema->getOperation() === 'create' && Facade::getSanctumExpiration()){
            $ret[] = Action::make('expires_at::default')
                ->tooltip('Refresh Default Expires At')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->action(static fn ($set) => $set('expires_at', Facade::getTokenDefaultExpiresAt()))
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

    protected static function makeViewTokenInput(Schema $schema): TextInput
    {
        $ret = TextInput::make('plainTextToken')
            ->label('Token')
            ->readonly()
            ->disabled()
            ->dehydrated(false)
            ->columnSpanFull()
            ->formatStateUsing(static fn (?string $state) => $state === null ? : $state)
            ->live();

//        $schema->getRecord()
//
//
//        $actionShow = Action::make('token::reveal')
//            ->icon(FilamentIcon::resolve(FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_SHOW_PASSWORD) ?? Heroicon::Eye)
//            ->defaultColor('gray')
//            ->tooltip('Reveal Token')
//            ->visible(static fn (TextInput $component) => $component->getMeta('isTokenRevealed') !== true)
//            ->action(static function (TextInput $component, Model $record) {
//                if (!$component->hasMeta('tokenValue')) {
//                    if (method_exists($record, 'getPlainTextToken')) {
//                        $component->meta(
//                            'tokenValue',
//                            $component->evaluate(\Closure::fromCallable([$record, 'getPlainTextToken']))
//                        );
//                    } else {
//                        $record->makeVisible('token');
//                        $component->meta('tokenValue', "{$record->getKey()}|{$record->token}");
//                        $record->makeHidden('token');
//                    }
//                }
//                $component->meta('isTokenRevealed', true);
//                $component->state($component->getMeta('tokenValue'));
//            });
//
//        $actionHide = Action::make('token::hide')
//            ->icon(FilamentIcon::resolve(FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_HIDE_PASSWORD) ?? Heroicon::EyeSlash)
//            ->defaultColor('gray')
//            ->tooltip('Hide Token')
//            ->visible(static fn (TextInput $component) => $component->getMeta('isTokenRevealed') === true)
//            ->action(static function (TextInput $component, $set) {
//                $component->state(str_repeat('*', 64));
//                $component->meta('isTokenRevealed', false);
//            });
////
//        $ret->suffixActions([$actionShow, $actionHide], true);
//        $ret->suffixActions([$actionShow], true);
//        $actionHide = HidePasswordAction::make('hideToken');
//        $actionCopy = CopyAction::make('copyToken');
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
            ->default($schema->getOperation() === 'create' ? Facade::getTokenDefaultExpiresAt() : null)
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
