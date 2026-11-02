{{--
    Zeitraum wählen (von–bis) in einem Feld: erst den Beginn anklicken, dann das Ende, dazwischen ist der Bereich
    hinterlegt. Gleiche Optik wie die Datumsauswahl (Klassen von Filaments DateTimePicker, Pfeile, Monat und Jahr als
    Auswahl, „Heute“, „Leeren“), Logik in Alpine `wmDateRangePicker` (wm-ui.js). Übernommen wird
    erst, wenn der Zeitraum fertig ist oder das Feld schließt (ohne Ende dann nur der Beginn), live gebundene Felder
    fragen so nicht nach jedem Klick nach.

    start/end: Livewire-Pfade der beiden Werte (Y-m-d), z. B. „data.starts_on“ oder „tableFilters.created_at.from“.
    Genutzt von WursterMedien\Ui\Forms\DateRangePicker (Formulare), den Zeitraum-Filtern (ToolbarFilters) und dem eigenen
    Zeitraum der Statistik.
--}}
@props([
    'start',
    'end',
    'live' => false,
    'min' => null,
    'max' => null,
    'clearable' => true,
    'endOptional' => false,
    'disabled' => false,
    'valid' => true,
    'placeholder' => 'Zeitraum wählen',
    'prefix' => null,
    'id' => null,
    'label' => null,
])

@php
    use Filament\Support\Icons\Heroicon;

@endphp

<x-filament::input.wrapper
    :disabled="$disabled"
    :valid="$valid"
    :prefix="$prefix"
    {{ $attributes->class(['fi-fo-date-time-picker', 'wm-drp']) }}
>
    <div
        x-data="wmDateRangePicker({
            start: $wire.$entangle(@js($start), @js((bool) $live)),
            end: $wire.$entangle(@js($end), @js((bool) $live)),
            min: @js($min),
            max: @js($max),
            endOptional: @js((bool) $endOptional),
            placeholder: @js($placeholder),
        })"
        wire:ignore
        wire:key="date-range.{{ $start }}.{{ substr(md5(serialize([$min, $max, $disabled, $endOptional])), 0, 12) }}"
        x-on:keydown.page-up.prevent="isOpen() && shiftMonth($event.shiftKey ? -12 : -1)"
        x-on:keydown.page-down.prevent="isOpen() && shiftMonth($event.shiftKey ? 12 : 1)"
    >
        <button
            x-ref="button"
            x-on:click="toggle()"
            type="button"
            @if ($id) id="{{ $id }}" @endif
            @if ($label) aria-label="{{ $label }}" @endif
            @disabled($disabled)
            class="fi-fo-date-time-picker-trigger"
        >
            <span class="fi-fo-date-time-picker-display-text-input wm-drp-text" x-bind:class="{ 'wm-drp-placeholder': ! hasValue }">
                <span x-text="text">{{ $placeholder }}</span>
                <span class="wm-drp-days" x-show="daysText" x-text="daysText"></span>
            </span>
        </button>

        <div
            x-ref="panel"
            x-cloak
            x-float.placement.bottom-start.offset.flip.shift="{ offset: 8 }"
            x-on:click.stop
            class="fi-fo-date-time-picker-panel wm-drp-panel"
        >
            <div class="fi-fo-date-time-picker-panel-header">
                <button type="button" class="wm-dp-nav" title="Vorheriger Monat" aria-label="Vorheriger Monat" x-on:click="shiftMonth($event.shiftKey ? -12 : -1)" x-bind:disabled="! canShift(-1)">
                    <x-filament::icon :icon="Heroicon::ChevronLeft" />
                </button>
                <select class="fi-fo-date-time-picker-month-select" aria-label="Monat" x-on:change="viewMonth = Number($event.target.value)">
                    <template x-for="(name, index) in months" x-bind:key="index">
                        <option x-bind:value="index" x-text="name" x-bind:selected="index === viewMonth"></option>
                    </template>
                </select>
                <select class="fi-fo-date-time-picker-month-select wm-dp-year" aria-label="Jahr" x-on:change="viewYear = Number($event.target.value)">
                    <template x-for="year in years" x-bind:key="year">
                        <option x-bind:value="year" x-text="year" x-bind:selected="year === viewYear"></option>
                    </template>
                </select>
                <button type="button" class="wm-dp-nav" title="Nächster Monat" aria-label="Nächster Monat" x-on:click="shiftMonth($event.shiftKey ? 12 : 1)" x-bind:disabled="! canShift(1)">
                    <x-filament::icon :icon="Heroicon::ChevronRight" />
                </button>
            </div>

            <div class="fi-fo-date-time-picker-calendar-header">
                @foreach (['Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.', 'So.'] as $weekday)
                    <div class="fi-fo-date-time-picker-calendar-header-day">{{ $weekday }}</div>
                @endforeach
            </div>

            <div role="grid" class="fi-fo-date-time-picker-calendar" x-on:mouseleave="hovered = null">
                <template x-for="cell in cells" x-bind:key="cell.iso">
                    <div
                        role="gridcell"
                        x-text="cell.day"
                        x-on:click="pick(cell)"
                        x-on:mouseenter="hovered = cell.iso"
                        x-bind:aria-selected="cell.iso === draftStart || cell.iso === draftEnd"
                        x-bind:class="dayClasses(cell)"
                        class="fi-fo-date-time-picker-calendar-day"
                    ></div>
                </template>
            </div>

            <div class="wm-dp-footer">
                <button type="button" class="wm-dp-action" data-wm-dp-action="today" x-on:click="pickToday()" x-bind:title="todayTitle">Heute</button>
                <span class="wm-drp-hint" x-text="hint"></span>
                @if ($clearable)
                    <button type="button" class="wm-dp-action" data-wm-dp-action="clear" title="Zeitraum entfernen" x-show="hasValue || draftStart" x-on:click="clear()">Leeren</button>
                @endif
            </div>
        </div>
    </div>
</x-filament::input.wrapper>
