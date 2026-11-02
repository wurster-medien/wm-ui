{{--
    Rahmen aller Mails der App (Laravel-Mailkomponente, überschrieben): 680 statt 570 px breit, damit Tabellen mit
    Preisen in Outlook nicht umbrechen (Simon, 16.10.2026); auf schmalen Bildschirmen volle Breite. Oben der dunkle Kopf
    (header.blade.php, Simon 1.11.2026), die erste Überschrift der Mail steht dort (MailHeading::lift).
--}}
@props(['title' => null, 'subtitle' => null, 'kicker' => null, 'tone' => null, 'meter' => null, 'customer' => null])
@php
    $content = WursterMedien\Ui\Mail\MailHeading::lift((string) Illuminate\Mail\Markdown::parse($slot));
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('wm-ui.owner') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 720px) {
.inner-body {
width: 100% !important;
border-radius: 0 !important;
}

.footer {
width: 100% !important;
}

.body {
padding-top: 0 !important;
}

.wm-band {
border-radius: 0 !important;
}
}

@media only screen and (max-width: 560px) {
.wm-cards-cell {
display: block !important;
width: 100% !important;
box-sizing: border-box;
margin-bottom: 8px;
}

.wm-cards-gap {
display: none !important;
}
}

@media only screen and (max-width: 500px) {
.wm-band {
padding: 24px 20px 28px !important;
}

.wm-band-title {
font-size: 28px !important;
}

.inner-body .content-cell {
padding: 28px 20px !important;
}

.wm-button-table {
width: 100% !important;
}

.button {
display: block !important;
text-align: center !important;
}
}
</style>
{!! $head ?? '' !!}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="680" cellpadding="0" cellspacing="0" role="presentation">
<x-mail::header :url="config('app.url')" :title="$title ?? $content['title']" :subtitle="$subtitle" :kicker="$kicker" :tone="$tone" :meter="$meter" :customer="$customer" />
<!-- Body content -->
<tr>
<td class="content-cell">
{!! $content['body'] !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
