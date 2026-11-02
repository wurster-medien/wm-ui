{{--
    Button der öffentlichen Seiten: primary (Hauptaktion, Freigeben, Verbinden, kräftig in Teal mit weißer Schrift), dark (Aktion
    in Listen, dunkel), secondary (Nebenaktion, Abbrechen, Drucken), warning (Bestätigen mit Folgen, z. B. Hinweise pausieren),
    danger (Ablehnen). Mit href ein Link, sonst ein Button (Standard type="submit").
--}}
@props(['variant' => 'primary', 'href' => null, 'icon' => null, 'size' => null])
@php
    throw_unless(in_array($variant, ['primary', 'dark', 'secondary', 'warning', 'danger'], true), InvalidArgumentException::class, "Unbekannte Button-Art {$variant}.");
    $classes = ['pub-btn', 'pub-btn-'.$variant, 'pub-btn-sm' => $size === 'sm'];
@endphp
@if ($href !== null)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>@if ($icon)<x-public.icon :name="$icon" />@endif{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class($classes) }}>@if ($icon)<x-public.icon :name="$icon" />@endif{{ $slot }}</button>
@endif
