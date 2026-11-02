{{-- Kleingedrucktes unter der Mail (überschrieben wie button.blade.php): Abstand und Linie an Zellen statt margin. --}}
<table class="subcopy" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0; padding: 0; border: 0;">
<tr>
<td style="padding-top: 25px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="border-top: 1px solid #e5e7eb; padding-top: 25px;">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
</table>
