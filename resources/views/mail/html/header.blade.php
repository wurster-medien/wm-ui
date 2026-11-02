{{--
    Dunkler Kopf aller Mails (Laravel-Mailkomponente, überschrieben, Simon 1.11.2026, Entwurf A): Logo der Plattform (logo.blade.php dort), rechts die
    Firma des Kunden, darunter Thema (kicker), Überschrift, Unterzeile und bei Speicherwarnungen die Belegung (meter).
    Mit tone="danger" rot. PNG statt SVG, weil Gmail und Outlook kein SVG zeigen; Farben auch als bgcolor für Outlook.
--}}
@props(['url', 'title' => null, 'subtitle' => null, 'kicker' => null, 'tone' => null, 'meter' => null, 'customer' => null])
@php
    $danger = $tone === 'danger';
    $bar = $danger ? '#ef4444' : '#14b8a6';
    $percent = $meter !== null ? max(1, min(100, (int) $meter)) : null;
@endphp
<tr>
<td class="wm-band" bgcolor="#242D33" style="background-color: #242D33;@if ($danger) border-top: 6px solid #ef4444;@endif">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="left" valign="middle">
<x-mail::logo :url="$url" />
</td>
@if (filled($customer))
<td class="wm-band-customer" align="right" valign="middle">{{ $customer }}</td>
@endif
</tr>
</table>
@if (filled($kicker))
<p class="wm-band-kicker" style="color: {{ $danger ? '#fca5a5' : '#5eead4' }};">{{ $kicker }}</p>
@endif
@if (filled($title))
<h1 class="wm-band-title"@if (blank($kicker)) style="margin-top: 36px;"@endif>{{ $title }}</h1>
@endif
@if (filled($subtitle))
<p class="wm-band-sub">{{ $subtitle }}</p>
@endif
@if ($percent !== null)
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 24px;">
<tr>
<td valign="middle">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="{{ $percent }}%" bgcolor="{{ $bar }}" style="background-color: {{ $bar }}; height: 10px; font-size: 0; line-height: 0; border-radius: 5px 0 0 5px;">&nbsp;</td>
@if ($percent < 100)
<td width="{{ 100 - $percent }}%" bgcolor="#46525a" style="background-color: #46525a; height: 10px; font-size: 0; line-height: 0; border-radius: 0 5px 5px 0;">&nbsp;</td>
@endif
</tr>
</table>
</td>
<td class="wm-band-meter" width="76" align="right" valign="middle">{{ $percent }} %</td>
</tr>
</table>
@endif
</td>
</tr>
