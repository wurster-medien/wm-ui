{{--
    Umschalter für eine Ansicht (Kalender: Feiertage, Anlässe, Ideen …): Chip, an = getönt mit Häkchen, aus = nur
    umrandet mit leerem Kreis. Das Icon hat immer seinen Platz, damit die Leiste beim Umschalten nicht springt.
    Schaltet die boolesche Livewire-Property `property` um, oder ruft die Livewire-Methode `action` auf (für gesperrte
    Properties). Stile: wm-ui.css (.wm-view-toggle).
--}}
@props([
    'property' => null,
    'action' => null,
    'active' => false,
])

<button
    type="button"
    wire:click="{{ $action ?? '$toggle(\''.$property.'\')' }}"
    aria-pressed="{{ $active ? 'true' : 'false' }}"
    {{ $attributes->class(['wm-view-toggle', 'wm-view-toggle-on' => $active]) }}
>
    @if ($active)
        <x-filament::icon icon="heroicon-m-check" class="wm-view-toggle-icon" />
    @else
        <span class="wm-view-toggle-icon wm-view-toggle-off-mark" aria-hidden="true"></span>
    @endif
    <span>{{ $slot }}</span>
</button>
