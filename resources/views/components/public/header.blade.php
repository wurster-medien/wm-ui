{{--
    Seitenkopf der öffentlichen Seiten (Layout public.layout): optional eine Zeile darüber (kicker), Überschrift,
    Unterzeile (z. B. „für <Kunde>“) und im Slot ein kurzer Einleitungstext. Steht im dunklen Kopf der Seite (Stack
    „hero“ im Layout, Simon 1.11.2026), egal wo die Seite den Kopf aufruft.
--}}
@props(['title', 'subtitle' => null, 'kicker' => null])
@push('hero')
    <header {{ $attributes->class('pub-head') }}>
        @if (filled($kicker))
            <p class="pub-kicker">{{ $kicker }}</p>
        @endif
        <h1>{{ $title }}</h1>
        @if (filled($subtitle))
            <p class="pub-sub">{{ $subtitle }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="pub-intro">{{ $slot }}</div>
        @endif
    </header>
@endpush
