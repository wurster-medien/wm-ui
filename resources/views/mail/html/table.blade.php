{{-- Tabelle aller Mails (überschrieben wie button.blade.php): Abstand als padding an einer Zelle statt margin. --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="padding: 8px 0 24px;">
<div class="table">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</div>
</td>
</tr>
</table>
