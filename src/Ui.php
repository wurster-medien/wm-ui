<?php

namespace WursterMedien\Ui;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Panel;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;

/**
 * Das eine Design aller Filament-Plattformen. Gleich für alle: Farben, Knöpfe (ActionStyle), Klick auf die Zeile
 * (RowClick), Filter in der Werkzeugleiste, Datumsauswahl und Zeitraum. Je Plattform bleiben Logo, Name und Fachliches.
 */
final class Ui
{
    /** Primärfarbe wurster medien: Teal wie auf der Agentur-Website (Tailwind Teal 500). */
    public const string PRIMARY_COLOR = '#14b8a6';

    /** Zelle einer Tabelle mit ->stackedOnMobile(), die am Handy ohne Beschriftung steht: ->extraCellAttributes(['class' => Ui::PLAIN_CELL]). */
    public const string PLAIN_CELL = 'wm-cell-plain';

    /** Gemeinsame Einstellungen eines Panels, jeder Panel-Provider ruft sie über ->tap(Ui::panel(...)) auf. */
    public static function panel(Panel $panel): Panel
    {
        return $panel
            /** Initialen als eigenes SVG statt ui-avatars.com, damit keine Namen an einen fremden Dienst gehen. */
            ->defaultAvatarProvider(InitialsAvatarProvider::class)
            ->maxContentWidth(Width::Full)
            ->colors([
                'primary' => Color::Teal,
                'gray' => Color::Zinc,
            ])
            ->renderHook(TablesRenderHook::TOOLBAR_GROUPING_SELECTOR_AFTER, fn (): string => ToolbarFilters::render())
            ->renderHook(PanelsRenderHook::SIMPLE_LAYOUT_START, fn (): string => view('filament.partials.guest-theme-switcher')->render());
    }

    /** Grundeinstellungen der Komponenten, einmal beim Start (UiServiceProvider). */
    public static function configure(): void
    {
        /** Feste Farben und Icons je Art eines Buttons (Bearbeiten, Löschen …), siehe ActionStyle. */
        ActionStyle::registerColors();
        ActionStyle::configureBuiltInActions();

        /**
         * Filter und Spaltenauswahl wirken sofort, ohne Knopf „Filter anwenden“. Filter stehen ohne Filter-Icon direkt in
         * der Werkzeugleiste (ToolbarFilters) und bleiben wie Sortierung und Gruppierung in der Sitzung. Filter nicht in
         * Relation-Managern: Deren Schlüssel gilt für die Klasse, Filterwerte eines Datensatzes passten sonst beim
         * nächsten nicht. Aktionen je Zeile erscheinen als Buttons statt als Links, Icon-Buttons bleiben.
         */
        Table::configureUsing(fn (Table $table): Table => $table
            ->modifyUngroupedRecordActionsUsing(fn (Action $action): Action => $action->isLink() ? $action->button() : $action)
            ->deferFilters(false)
            ->filtersLayout(FiltersLayout::Hidden)
            ->hiddenFilterIndicators()
            ->groupingDirectionSettingHidden()
            ->deferColumnManager(false)
            ->persistFiltersInSession(fn (Table $table): bool => ! $table->getLivewire() instanceof RelationManager)
            ->persistSortInSession()
            ->persistGroupInSession()
            ->emptyStateActions([
                Action::make('resetFiltersFromEmptyState')
                    ->label('Filter zurücksetzen')
                    ->tap(ActionStyle::Secondary)
                    ->visible(fn (HasTable $livewire): bool => ToolbarFilters::differFromDefaults($livewire))
                    ->action(fn (HasTable $livewire) => $livewire->resetTableFiltersForm()),
            ])
            ->reorderRecordsTriggerAction(fn (Action $action, bool $isReordering): Action => $action
                ->button()
                ->label($isReordering ? 'Fertig' : 'Reihenfolge ändern')
                ->tap($isReordering ? ActionStyle::Confirm : ActionStyle::Secondary)));

        /** „… hinzufügen“ unter Repeatern und Bausteinen (Builder) ist ein Anlegen (ActionStyle::Create). */
        Repeater::configureUsing(fn (Repeater $repeater): Repeater => $repeater
            ->addAction(fn (Action $action): Action => $action->tap(ActionStyle::Create)));
        Builder::configureUsing(fn (Builder $builder): Builder => $builder
            ->addAction(fn (Action $action): Action => $action->tap(ActionStyle::Create)));

        /**
         * Datumsauswahl immer ohne Browser-Picker und mit deutschem Format (nicht je Feld setzen). Sie bekommt Pfeile für
         * die Monate, „Heute“, „Leeren“ (ohne Pflicht) und einen deutlich markierten heutigen Tag (wm-ui.js). Das Datum
         * lässt sich auch eintippen (12.11.2026, 12.11.26, 121126 …; Simon, 12.11.2026), außer das Feld ist gesperrt.
         */
        DateTimePicker::configureUsing(fn (DateTimePicker $picker): DateTimePicker => $picker
            ->native(false)
            ->displayFormat(fn (DateTimePicker $component): string => $component->hasTime() ? 'd.m.Y H:i' : 'd.m.Y')
            ->extraAlpineAttributes(
                fn (DateTimePicker $component): array => $component->isNative() ? [] : [
                    /** Ohne doppelte Anführungszeichen: Filament schreibt das Attribut ungeschützt ins HTML. */
                    'x-init' => sprintf(
                        'window.WmDatePicker?.enhance($el, $data, $watch, { clearable: %s, typeable: %s, time: %s })',
                        $component->isRequired() ? 'false' : 'true',
                        $component->isDisabled() || $component->isReadOnly() ? 'false' : 'true',
                        $component->hasTime() ? 'true' : 'false',
                    ),
                ],
                merge: true,
            ));
    }
}
