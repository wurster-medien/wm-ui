<?php

namespace WursterMedien\Ui;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Model;

/**
 * Avatare für Nutzer und Kunden (Nutzermenü, Kunden-Umschalter) als Initialen im eigenen SVG.
 *
 * Filament holt sie sonst bei ui-avatars.com, dann ginge bei jedem Seitenaufruf der Name an einen fremden Dienst
 * (Datenschutz). Hier entsteht das Bild als data:-URI in der Plattform, ohne Anfrage nach außen. Farben wie bei Filament:
 * dunkles Grau des Panels, weiße Schrift. Die Initialen stehen maskiert im SVG.
 *
 * @see https://filamentphp.com/docs/5.x/users/overview#using-a-different-avatar-provider
 */
class InitialsAvatarProvider implements AvatarProvider
{
    /** Höchstens so viele Buchstaben, damit sie in den runden Avatar passen. */
    public const MAX_INITIALS = 2;

    public function get(Model $record): string
    {
        $initials = self::initials(Filament::getNameForDefaultAvatar($record));
        $background = Color::convertToHex(FilamentColor::getColor('gray')[950] ?? Color::Gray[950]);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="'.htmlspecialchars($background, ENT_QUOTES | ENT_XML1).'"/>'
            .'<text x="32" y="32" dy="0.35em" text-anchor="middle" fill="#FFFFFF" font-size="26" font-weight="600"'
            .' font-family="ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif">'
            .htmlspecialchars($initials, ENT_QUOTES | ENT_XML1)
            .'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /** Erster Buchstabe bzw. erste Ziffer je Wort, führende Satzzeichen übersprungen (wie bei Filament), groß. */
    public static function initials(string $name): string
    {
        $letters = collect(preg_split('/\s+/u', trim($name)) ?: [])
            ->map(fn (string $word): string => mb_substr((string) preg_replace('/^[^\p{L}\p{N}]+/u', '', $word), 0, 1))
            ->filter(fn (string $letter): bool => $letter !== '')
            ->take(self::MAX_INITIALS)
            ->implode('');

        return mb_strtoupper($letters);
    }
}
