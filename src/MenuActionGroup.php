<?php

namespace WursterMedien\Ui;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

/**
 * Menü der Kopfzeile (MoreActions::make und ::menu) mit einer Ausnahme (v0.2.59, Simon 19.11.2026: „unter Mehr nur ein Button macht
 * keinen Sinn“): Bleibt nach der Sichtbarkeit genau eine Aktion übrig, steht sie direkt als Knopf in der Kopfzeile statt allein im Menü.
 * Ohne eigene Farbe bekommt sie die graue Farbe des Menüs (ActionStyle::Secondary). Ab zwei Aktionen bleibt es das Menü.
 */
class MenuActionGroup extends ActionGroup
{
    public function toEmbeddedHtml(): string
    {
        $single = $this->singleVisibleAction();

        if ($single === null) {
            return parent::toEmbeddedHtml();
        }

        if ($single->getColor() === null) {
            $single->color(ActionStyle::Secondary->color());
        }

        return $single->defaultView(Action::BUTTON_VIEW)->toHtml();
    }

    /** Die einzige sichtbare Aktion des Menüs (auch in Abschnitten), sonst null. */
    public function singleVisibleAction(): ?Action
    {
        if ($this->isButtonGroup() || ! $this->hasDropdown()) {
            return null;
        }

        $visible = array_values(array_filter($this->getFlatActions(), fn (Action $action): bool => $action->isVisible()));

        return count($visible) === 1 ? $visible[0] : null;
    }
}
