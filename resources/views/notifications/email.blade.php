{{--
    Mails der Notifications (Laravel, überschrieben): Gruß ohne Salutation mit dem Namen aus wm-ui.owner statt APP_NAME (Simon, 20.10.2026).
    Der Betreff steht als Überschrift im dunklen Kopf (MailHeading::ofSubject, Simon 1.11.2026). Über ->markdown('notifications::email', […])
    optional: kicker (Thema), meter (Belegung in Prozent), customer (Firma), alert (roter Kasten nach dem ersten Satz).
--}}
@php
    $heading = WursterMedien\Ui\Mail\MailHeading::ofSubject((string) ($subject ?? ''));
@endphp
<x-mail::message :title="$heading['title']" :subtitle="$heading['subtitle']" :kicker="$kicker ?? $heading['kicker']" :tone="$level === 'error' ? 'danger' : null" :meter="$meter ?? null" :customer="$customer ?? null">
{{-- Greeting --}}
@if (! empty($greeting))
{{ $greeting }}
@else
@if ($level === 'error')
@lang('Whoops!')
@else
@lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@if ($loop->first && ! empty($alert))
<x-mail::panel tone="danger">
{{ $alert }}
</x-mail::panel>

@endif
@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards,')<br>
{{ config('wm-ui.owner') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
