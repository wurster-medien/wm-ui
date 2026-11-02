<?php

namespace WursterMedien\Ui;

/**
 * Brotkrumen auf jeder Seite, damit die Überschrift beim Wechsel zwischen Seiten nicht springt (Simon, 20.10.2026).
 * Resource-Seiten haben sie von Filament, eigene Seiten über diesen Trait wie die Listen von Filament: „Titel ›
 * Übersicht“, Unterseiten mit ihren Eltern aus breadcrumbParents() vor dem Titel. BreadcrumbsTest prüft jede Seite.
 */
trait HasBreadcrumbs
{
    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        $parents = $this->breadcrumbParents();

        if ($parents === []) {
            return [static::getUrl() => static::getNavigationLabel(), __('filament-panels::resources/pages/list-records.breadcrumb')];
        }

        return [...$parents, $this->getBreadcrumb() ?? (string) $this->getTitle()];
    }

    /**
     * Übergeordnete Seiten (Adresse => Name, ohne Adresse nur Text), leer für Seiten der obersten Ebene.
     *
     * @return array<int|string, string>
     */
    protected function breadcrumbParents(): array
    {
        return [];
    }
}
