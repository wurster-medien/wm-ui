<?php

namespace WursterMedien\Ui;

use Filament\Schemas\Components\Component;

/**
 * Nur ein Reiter ist keine Auswahl (Simon, 20.10.2026: „wenn nur 1 Reiter, dann nicht anzeigen“). Für Seiten mit
 * getTabs(): die Leiste fehlt, der Reiter bleibt aktiv und filtert weiter (HidesSingleTabTest prüft jede Seite).
 */
trait HidesSingleTab
{
    public function getTabsContentComponent(): Component
    {
        $component = parent::getTabsContentComponent();

        return count($this->getCachedTabs()) > 1 ? $component : $component->hidden();
    }
}
