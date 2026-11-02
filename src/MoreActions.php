<?php

namespace WursterMedien\Ui;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * Menü „Mehr“ in der Kopfzeile einer Seite (Admin und Portal): seltene, rückwärts gerichtete und löschende Aktionen,
 * damit oben nur die Hauptschritte als Buttons stehen. Mehrere Themen stehen in Abschnitten (section), Löschen kommt
 * als Letztes. In Karten (forRecord) nur als Icon-Button, damit die Aktionen in eine Zeile passen.
 */
class MoreActions
{
    /**
     * @param  list<Action|ActionGroup>  $actions
     */
    public static function make(array $actions): ActionGroup
    {
        return ActionGroup::make($actions)
            ->label('Mehr')
            ->icon(Heroicon::OutlinedEllipsisVertical)
            ->color(ActionStyle::Secondary->color())
            ->dropdownWidth(Width::ThreeExtraSmall)
            ->button();
    }

    /**
     * Abschnitt innerhalb eines Menüs, mit Trennlinie zum nächsten. Ohne sichtbare Aktion verschwindet er.
     *
     * @param  list<Action|ActionGroup>  $actions
     */
    public static function section(array $actions): ActionGroup
    {
        return ActionGroup::make($actions)->dropdown(false);
    }

    /**
     * Menü mit eigener Beschriftung für zusammengehörige Schritte in einer Kopfzeile (z. B. „Angebot“, „Mitteilen“
     * unter „Paket und Upgrades“). Ohne sichtbare Aktion verschwindet es.
     *
     * @param  list<Action|ActionGroup>  $actions
     */
    public static function menu(string $label, Heroicon $icon, array $actions): ActionGroup
    {
        return ActionGroup::make($actions)
            ->label($label)
            ->icon($icon)
            ->color(ActionStyle::Secondary->color())
            ->dropdownWidth(Width::ThreeExtraSmall)
            ->button();
    }

    /**
     * Dasselbe Menü an einer Karte (Tabellen mit contentGrid): grauer Icon-Button mit drei Punkten, steht rechts.
     *
     * @param  list<Action|ActionGroup>  $actions
     */
    public static function forRecord(array $actions): ActionGroup
    {
        return ActionGroup::make($actions)
            ->label('Mehr')
            ->icon(Heroicon::OutlinedEllipsisVertical)
            ->color(ActionStyle::Secondary->color())
            ->dropdownWidth(Width::ThreeExtraSmall)
            ->iconButton();
    }
}
