{{--
    Anteilsbalken (v0.2.56): Abschnitte nebeneinander mit ihrem Anteil in Prozent, z. B. Kalkulation im Angebot (Arbeit, Fremdkosten,
    Marge). Als SVG, damit die Breiten Attribute sind und keine Ansicht ein style-Attribut braucht (DesignRules). Farben je Abschnitt
    über die Klasse im CSS (fill), Höhe, Hintergrund und Rundung über die Klasse am Balken. Werte unter 0 zählen als 0, die Summe
    wird bei 100 abgeschnitten. Nur Schmuck: aria-hidden, die Zahlen stehen daneben als Text.

    @var array<string, int|float> $parts  Klasse => Anteil in Prozent, in der Reihenfolge von links
--}}
@props(['parts' => []])
@php
    $offset = 0.0;
    $segments = [];

    foreach ($parts as $class => $share) {
        $width = max(0.0, min((float) $share, 100.0 - $offset));
        $segments[] = ['class' => $class, 'x' => $offset, 'width' => $width];
        $offset += $width;
    }
@endphp
<svg {{ $attributes->class('wm-share-bar') }} viewBox="0 0 100 10" preserveAspectRatio="none" aria-hidden="true" focusable="false">
    @foreach ($segments as $segment)
        <rect class="{{ $segment['class'] }}" x="{{ round($segment['x'], 3) }}" y="0" width="{{ round($segment['width'], 3) }}" height="10" />
    @endforeach
</svg>
