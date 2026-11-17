{{--
    Blättern durch Monate bzw. Wochen und Tage, überall gleich (Kalender im Kundenbereich und im Social Hub): außen die
    Pfeile, dazwischen „Heute“ und die Monatsauswahl zum direkten Springen. `previous`, `today`, `next` und `jump`
    (bekommt Y-m) sind Livewire-Methoden, `month` ist der gezeigte Monat (Y-m), `max` der letzte wählbare. `label` steht
    daneben, wenn der Zeitraum nicht der Monat ist (Woche, Tag). `unit` ist month, week oder day und bestimmt nur die
    Beschriftung der Pfeile. `confirm` fragt vor jedem Wechsel nach (z. B. wenn er Eingaben leert). `today-visible`
    blendet „Heute“ aus, wenn man schon dort ist. `timezone` ist die Anzeige-Zeitzone (Standard: die der Anwendung).
    Stile: wm-ui.css (.wm-period-nav).
--}}
@props([
    'month',
    'label' => null,
    'previous' => 'previousMonth',
    'today' => 'currentMonth',
    'next' => 'nextMonth',
    'jump' => 'goToMonth',
    'unit' => 'month',
    'max' => null,
    'confirm' => null,
    'todayVisible' => true,
    'timezone' => null,
])

@php
    $previousLabel = ['week' => 'Vorherige Woche', 'day' => 'Vorheriger Tag'][$unit] ?? 'Vorheriger Monat';
    $nextLabel = ['week' => 'Nächste Woche', 'day' => 'Nächster Tag'][$unit] ?? 'Nächster Monat';

    $timezone ??= (string) config('wm-ui.timezone', config('app.timezone'));
    $selected = \Carbon\CarbonImmutable::createFromFormat('!Y-m', $month, $timezone);
    $current = \Carbon\CarbonImmutable::now($timezone)->startOfMonth();
    $last = $max !== null ? \Carbon\CarbonImmutable::createFromFormat('!Y-m', $max, $timezone) : null;
    $from = $selected->min($current)->subMonthsNoOverflow(12);
    $until = $selected->max($current)->addMonthsNoOverflow(12);
    $until = $last !== null ? $until->min($last) : $until;

    $options = [];

    for ($option = $from; $option->lessThanOrEqualTo($until); $option = $option->addMonthNoOverflow()) {
        $options[$option->format('Y-m')] = $option->locale('de')->translatedFormat('F Y');
    }

    $nextDisabled = $last !== null && $selected->greaterThanOrEqualTo($last);
@endphp

<div
    {{ $attributes->class('wm-period-nav') }}
    data-confirm="{{ $confirm }}"
    x-data
    x-on:click.capture="if ($root.dataset.confirm && $event.target.closest('button') && ! window.confirm($root.dataset.confirm)) { $event.preventDefault(); $event.stopPropagation(); }"
>
    <x-filament::button color="gray" icon="heroicon-o-chevron-left" label-sr-only :tooltip="$previousLabel" wire:click="{{ $previous }}">
        {{ $previousLabel }}
    </x-filament::button>

    @if ($todayVisible)
        <x-filament::button color="gray" wire:click="{{ $today }}">Heute</x-filament::button>
    @endif

    <x-filament::input.wrapper class="wm-period-nav-month">
        <x-filament::input.select
            aria-label="Monat"
            wire:key="period-nav-{{ $month }}"
            x-on:change="if ($root.dataset.confirm && ! window.confirm($root.dataset.confirm)) { $el.value = {{ \Illuminate\Support\Js::from($month) }}; return; } $wire.call({{ \Illuminate\Support\Js::from($jump) }}, $el.value)"
        >
            @foreach ($options as $value => $optionLabel)
                <option value="{{ $value }}" @selected($value === $month)>{{ $optionLabel }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>

    <x-filament::button color="gray" icon="heroicon-o-chevron-right" label-sr-only :tooltip="$nextLabel" wire:click="{{ $next }}" :disabled="$nextDisabled">
        {{ $nextLabel }}
    </x-filament::button>

    @if ($label !== null)
        <h2 class="wm-period-nav-label">{{ $label }}</h2>
    @endif
</div>
