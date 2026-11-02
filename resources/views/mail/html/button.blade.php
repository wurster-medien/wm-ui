{{--
    Button aller Mails (Laravel-Mailkomponente, überschrieben, Simon 20.10.2026): Abstand und Farbe an Tabellenzellen,
    weil Outlook unter Windows margin an Tabellen und Rahmen am Link ignoriert (Button klebte am Text, grauer Rand).
    Gleiche Form wie die Buttons der Rundmails (mail.mailing-block). Teal mit weißer Schrift (Simon, 1.11.2026, Variante W3, Kontrast 4,8:1).
--}}
@props([
    'url',
    'color' => 'primary',
    'align' => 'left',
])
@php
    [$background, $text] = match ($color) {
        'success', 'green' => ['#16a34a', '#ffffff'],
        'error', 'red' => ['#dc2626', '#ffffff'],
        default => ['#0c8074', '#ffffff'],
    };
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0;">
<tr>
<td align="{{ $align }}" style="padding: 14px 0 30px;">
<table class="wm-button-table" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="wm-button-cell" align="center" bgcolor="{{ $background }}" style="background-color: {{ $background }}; border-radius: 10px; padding: 12px 24px;">
<a href="{{ $url }}" class="button" target="_blank" rel="noopener" style="color: {{ $text }}; display: block; line-height: 20px; text-decoration: none;">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
