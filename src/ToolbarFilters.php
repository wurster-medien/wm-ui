<?php

namespace WursterMedien\Ui;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Column;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Livewire\Livewire;
use LogicException;
use Throwable;

/**
 * Alle Tabellenfilter direkt in der Werkzeugleiste (neben „Gruppieren nach“ und der Suche), ohne Filter-Icon und ohne
 * Knopf: Die Tabellen haben global FiltersLayout::Hidden (AppServiceProvider), der Render-Hook in beiden Panels zeichnet
 * jeden Filter als Feld, das direkt in tableFilters schreibt (wirkt sofort, deferFilters ist global aus).
 *
 * Arten: Auswahl (SelectFilter), Mehrfachauswahl (->multiple(), Häkchen im Dropdown), Ja/Nein (TernaryFilter,
 * TrashedFilter), Zeitraum (Filter mit zwei DatePicker-Feldern von und bis, gezeichnet als <x-date-range-picker>) und Schalter (Filter ohne Felder).
 *
 * Tabellen ohne Spaltenköpfe (Kacheln, ->content() oder Spalten-Layout) bekommen hier auch „Sortieren nach“, statt
 * einer eigenen Zeile über dem Inhalt (die blendet table-toolbar.blade.php aus).
 *
 * Am Handy klappt alles hinter dem Knopf „Filter“ auf, mit der Zahl der geänderten Filter daran (Simon, 2.11.2026: fünf
 * Filter untereinander schoben die Liste aus dem Bild). Das steuert nur wm-ui.css, am Rechner gibt es den Knopf nicht.
 */
class ToolbarFilters
{
    /** Render-Hook TablesRenderHook::TOOLBAR_GROUPING_SELECTOR_AFTER, leer für Tabellen mit sichtbarem Filterlayout. */
    public static function render(): string
    {
        $livewire = Livewire::current();

        if (! $livewire instanceof HasTable) {
            return '';
        }

        $table = $livewire->getTable();
        $count = self::count($livewire);
        $sorting = self::sorting($table);

        if ($table->getFiltersLayout() !== FiltersLayout::Hidden) {
            return $count.$sorting;
        }

        $filters = [];
        $changed = 0;

        foreach ($table->getFilters() as $filter) {
            if ($filter->isHidden()) {
                continue;
            }

            $field = self::field($filter, (array) data_get($livewire->tableFilters ?? [], $filter->getName(), []));

            if ($field === null) {
                if (app()->runningUnitTests()) {
                    throw new LogicException('Filter '.$filter->getName().' ('.$filter::class.') kann nicht in der Werkzeugleiste stehen.');
                }

                continue;
            }

            $filters[] = $field;
            $changed += self::differs($filter, $livewire) ? 1 : 0;
        }

        /** Schalter (Häkchen) stehen hinter den Auswahlfeldern, nicht mittendrin. */
        $filters = [
            ...array_filter($filters, fn (array $field): bool => $field['type'] !== 'toggle'),
            ...array_filter($filters, fn (array $field): bool => $field['type'] === 'toggle'),
        ];

        if ($filters === []) {
            return $count.$sorting;
        }

        return view('filament.partials.toolbar-filters', [
            'filters' => $filters,
            'changed' => $changed,
            'count' => new HtmlString($count),
            'sorting' => new HtmlString($sorting),
        ])->render();
    }

    /**
     * Gesamtzahl der Einträge mit Filtern und Suche („314 Einträge“, Simon 28.10.2026: in keiner Liste stand sie; die
     * Blätter-Leiste nennt sie nur bei mehreren Seiten und nicht bei „Alle“). Ohne zählbare Abfrage nichts.
     */
    private static function count(HasTable $livewire): string
    {
        try {
            $count = $livewire->getAllTableRecordsCount();
        } catch (Throwable) {
            return '';
        }

        return '<span class="wm-table-count" style="align-self: center; font-size: 0.875rem; color: var(--gray-500); white-space: nowrap">'
            .e(Number::format($count).' '.($count === 1 ? 'Eintrag' : 'Einträge')).'</span>';
    }

    /**
     * Ob mindestens ein Filter vom Standard der Tabelle abweicht. Grundlage für „Filter zurücksetzen“ in leeren Tabellen
     * (AppServiceProvider), weil Filter in der Sitzung gespeichert bleiben, auch wenn sie in der Adresse nicht stehen
     * (z. B. „Ohne Paket“ nach einem Klick auf den Hinweis im Dashboard).
     */
    public static function differFromDefaults(HasTable $livewire): bool
    {
        foreach ($livewire->getTable()->getFilters() as $filter) {
            if (self::differs($filter, $livewire)) {
                return true;
            }
        }

        return false;
    }

    private static function differs(BaseFilter $filter, HasTable $livewire): bool
    {
        $state = (array) data_get($livewire->tableFilters ?? [], $filter->getName(), []);
        $default = $filter->getDefaultState();

        return match (true) {
            $filter instanceof SelectFilter && $filter->isMultiple() => self::normalizedValues($state['values'] ?? []) !== self::normalizedValues($default),
            /** Ohne leeren Eintrag steht ein leerer Wert (alte Adresse, Sitzung) für den Standard, siehe placeholder(). */
            $filter instanceof SelectFilter && ! $filter->canSelectPlaceholder() && blank($state['value'] ?? null) => false,
            $filter instanceof TernaryFilter, $filter instanceof SelectFilter => (string) ($state['value'] ?? '') !== (string) ($default ?? ''),
            $filter instanceof Filter && ! $filter->hasSchema() => (bool) ($state['isActive'] ?? false) !== (bool) $default,
            $filter instanceof Filter => array_filter($state, fn (mixed $value): bool => filled($value)) !== [],
            default => $filter->getActiveCount() > 0,
        };
    }

    /**
     * @return list<string>
     */
    private static function normalizedValues(mixed $values): array
    {
        $values = array_map(strval(...), array_filter((array) $values, fn (mixed $value): bool => filled($value)));
        sort($values);

        return $values;
    }

    /**
     * „Sortieren nach“ für Tabellen ohne Spaltenköpfe, wie Filament es sonst über dem Inhalt zeigt.
     */
    private static function sorting(Table $table): string
    {
        if ($table->isReordering() || ($table->getContent() === null && ! $table->hasColumnsLayout())) {
            return '';
        }

        $columns = array_filter($table->getVisibleColumns(), fn (Column $column): bool => $column->isSortable());

        if ($columns === []) {
            return '';
        }

        return view('filament.partials.toolbar-sorting', [
            'columns' => array_map(fn (Column $column): array => ['name' => $column->getName(), 'label' => $column->getLabel()], array_values($columns)),
            'defaultLabel' => $table->getDefaultSortOptionLabel(),
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $state  Aktueller Zustand des Filters aus tableFilters.
     * @return array<string, mixed>|null
     */
    private static function field(BaseFilter $filter, array $state): ?array
    {
        $base = ['name' => $filter->getName(), 'label' => $filter->getLabel()];

        if ($filter instanceof TernaryFilter) {
            $placeholder = $filter->getPlaceholder();

            return $base + [
                'type' => 'select',
                'placeholder' => blank($placeholder) || $placeholder === '-' ? 'Alle' : $placeholder,
                'options' => ['1' => $filter->getTrueLabel(), '0' => $filter->getFalseLabel()],
            ];
        }

        if ($filter instanceof SelectFilter) {
            $options = $filter->getOptions();

            if (! $filter->isMultiple()) {
                return $base + ['type' => 'select', ...self::placeholder($filter, $options, $state), 'options' => $options];
            }

            $selected = array_values(array_filter((array) ($state['values'] ?? []), fn (mixed $value): bool => filled($value)));

            return $base + [
                'type' => 'multiple',
                'options' => $options,
                'summary' => match (count($selected)) {
                    0 => 'Alle',
                    1 => (string) ($options[$selected[0]] ?? $selected[0]),
                    default => count($selected).' ausgewählt',
                },
            ];
        }

        if (! $filter instanceof Filter) {
            return null;
        }

        if (! $filter->hasSchema()) {
            return $base + ['type' => 'toggle'];
        }

        $dates = [];

        foreach ($filter->getSchemaComponents() as $component) {
            if (! $component instanceof DatePicker) {
                return null;
            }

            $dates[] = ['name' => $component->getName(), 'label' => $component->getLabel()];
        }

        if (count($dates) !== 2) {
            return null;
        }

        return $base + ['type' => 'dates', 'fields' => $dates];
    }

    /**
     * Leerer Eintrag einer Auswahl: „Alle“, außer der Filter hat ->selectablePlaceholder(false) (Simon, 2.11.2026: dort
     * stand „Alle“ doppelt, und der leere Wert zeigte nicht alles, sondern den Standard des Filters). Hat so ein Filter
     * aus einer alten Adresse oder Sitzung noch den leeren Wert, nennt ein unsichtbarer Eintrag diesen Standard, sonst
     * bliebe das Feld leer.
     *
     * @param  array<int|string, mixed>  $options
     * @param  array<string, mixed>  $state
     * @return array{placeholder: ?string, placeholderHidden: bool}
     */
    private static function placeholder(SelectFilter $filter, array $options, array $state): array
    {
        if ($filter->canSelectPlaceholder()) {
            return ['placeholder' => 'Alle', 'placeholderHidden' => false];
        }

        $label = blank($state['value'] ?? null) ? ($options[$filter->getDefaultState()] ?? null) : null;

        return ['placeholder' => $label, 'placeholderHidden' => true];
    }
}
