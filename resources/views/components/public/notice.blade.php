{{-- Hinweis der öffentlichen Seiten: success, error, info oder neutral (Standard). Fehler mit role="alert". --}}
@props(['type' => 'neutral'])
@php
    throw_unless(in_array($type, ['success', 'error', 'info', 'neutral'], true), InvalidArgumentException::class, "Unbekannte Hinweis-Art {$type}.");
@endphp
<div {{ $attributes->merge(['role' => $type === 'error' ? 'alert' : 'status'])->class(['notice', $type => $type !== 'neutral']) }}>{{ $slot }}</div>
