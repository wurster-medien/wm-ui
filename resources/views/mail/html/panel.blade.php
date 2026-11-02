{{--
    Hinweiskasten aller Mails (überschrieben wie button.blade.php): Abstand als padding an einer Zelle statt margin.
    tone: danger (rot) für das, was schiefgeht, warning (gelb) für Wichtiges, sonst Teal (Simon, 1.11.2026).
--}}
@props(['tone' => null])
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="padding: 5px 0 21px;">
<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0;">
<tr>
<td class="panel-content{{ in_array($tone, ['danger', 'warning'], true) ? ' panel-'.$tone : '' }}">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-item">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
