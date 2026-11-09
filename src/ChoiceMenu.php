<?php

namespace WursterMedien\Ui;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;

/**
 * Knopf mit Menü, der eine Auswahl zeigt und ändert, z. B. „Stand: In Bearbeitung ▾“ in der Kopfzeile einer Seite. Das
 * Menü sieht aus wie das der Auswahlfelder (Tabellenfilter, Formulare; `wm-ui.js`, Simon 9.11.2026: „das Dropdown aus
 * den Filtern übernehmen“): der aktuelle Eintrag in Teal mit Haken rechts und nicht wählbar, die übrigen ohne Icon
 * bündig darüber und darunter. Für Menüs mit Aktionen bleibt `MoreActions`.
 */
class ChoiceMenu
{
    /**
     * @param  list<Action>  $options  Einträge über `option()`
     */
    public static function make(array $options): ActionGroup
    {
        return ActionGroup::make($options)
            ->icon(Heroicon::OutlinedChevronDown)
            ->iconPosition(IconPosition::After)
            ->color(ActionStyle::Secondary->color())
            ->button();
    }

    /**
     * Eintrag des Menüs; `$current` sagt, ob er gerade gewählt ist.
     *
     * @param  Closure(): bool  $current
     */
    public static function option(Action $action, Closure $current): Action
    {
        return $action
            ->icon(fn (): ?Heroicon => $current() ? Heroicon::Check : null)
            ->disabled(fn (): bool => $current())
            ->extraAttributes(fn (): array => $current() ? ['class' => 'wm-choice-current', 'aria-current' => 'true'] : []);
    }
}
