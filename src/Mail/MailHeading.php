<?php

namespace WursterMedien\Ui\Mail;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Überschrift im dunklen Kopf aller Mails (Simon, 1.11.2026, Entwurf A). Mails mit eigener Überschrift (`# …` im Markdown)
 * geben sie über lift() an den Kopf ab, Mails aus MailMessage nehmen den Betreff über ofSubject(). Zusätze wie „Kopie:“
 * stehen dann als kleine Zeile darüber, eine Adresse hinter dem Doppelpunkt darunter.
 */
final class MailHeading
{
    /** @var list<string> */
    private const array PREFIXES = ['Kopie', 'Erinnerung'];

    /**
     * @return array{kicker: string|null, title: string, subtitle: string|null}
     */
    public static function ofSubject(string $subject): array
    {
        $kickers = [];

        while (($prefix = collect(self::PREFIXES)->first(fn (string $prefix): bool => str_starts_with($subject, $prefix.': '))) !== null) {
            $kickers[] = $prefix;
            $subject = Str::after($subject, $prefix.': ');
        }

        $address = Str::afterLast($subject, ': ');
        $hasAddress = $address !== $subject && str_contains($address, '@');

        return [
            'kicker' => $kickers !== [] ? implode(' · ', $kickers) : null,
            'title' => $hasAddress ? Str::beforeLast($subject, ': ') : $subject,
            'subtitle' => $hasAddress ? $address : null,
        ];
    }

    /**
     * Holt die erste Überschrift aus dem fertigen HTML, damit sie im Kopf steht. Nur vom Anfang der Mail: Rundmails haben
     * Bausteine als Tabellen und Bilder davor, eine Überschrift dort gehört an ihre Stelle.
     *
     * @return array{title: HtmlString|null, body: HtmlString}
     */
    public static function lift(string $html): array
    {
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>\s*/s', $html, $match, PREG_OFFSET_CAPTURE) !== 1) {
            return ['title' => null, 'body' => new HtmlString($html)];
        }

        [$heading, $offset] = $match[0];

        if (preg_match('/<(table|img)\b/i', substr($html, 0, $offset)) === 1) {
            return ['title' => null, 'body' => new HtmlString($html)];
        }

        return [
            'title' => new HtmlString($match[1][0]),
            'body' => new HtmlString(substr($html, 0, $offset).substr($html, $offset + strlen($heading))),
        ];
    }
}
