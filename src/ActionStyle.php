<?php

namespace WursterMedien\Ui;

use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Icons\Heroicon;

/**
 * Feste Farbe je Art eines Buttons, gleich in Admin und Portal, bei Anlegen, Anzeigen, Bearbeiten, Duplizieren und
 * Löschen auch mit festem Icon. Aktionen setzen Farben nur hierüber (`->tap(ActionStyle::Edit)`), ein Test prüft das.
 * Filaments eigene Aktionen (EditAction, DeleteAction …) bekommen ihren Stil in configureBuiltInActions().
 *
 * Die übrigen Arten behalten ihr eigenes Icon, damit etwa „Sperren“, „Trennen“ und „Ablehnen“ unterscheidbar bleiben.
 *
 * Aktionen sind Buttons, nie Links (keine Link-Ansicht, kein Blade-Link von Filament, Test). Zeilen von
 * Filament-Tabellen stellt der AppServiceProvider auf kleine Buttons, Zeilen eigener Tabellen (Blade) row(): kleiner
 * Button mit Icon und Text, keine Icon-Buttons ohne Text. Reihenfolge in Zeilen und unter Abschnitten: Anzeigen,
 * Kopieren, Hauptschritt (Freigeben, Zustellen, Einschalten), Bearbeiten, Nebenaktionen, Neu erzeugen,
 * Sperren/Entfernen, Löschen als Letztes. Seltenes, Rückwärtsgerichtetes und Löschen kommen bei vielen Aktionen ins
 * Menü „Mehr“ (MoreActions).
 */
enum ActionStyle
{
    /** Anlegen, Hinzufügen, Zuordnen: Teal, Plus (auch „… hinzufügen“ unter Repeatern und eigene Buttons mit Plus). */
    case Create;

    /** Anzeigen, Öffnen: Blau, Auge. */
    case View;

    /** Bearbeiten, Ändern: Indigo, Stift. */
    case Edit;

    /** Duplizieren, Kopie, neu auflegen: Violett, zwei Blätter. */
    case Duplicate;

    /** Löschen, Entfernen: Rot, Papierkorb. */
    case Delete;

    /** Trennen, Sperren, Ablehnen, Zuordnung lösen: Rot mit eigenem Icon. */
    case Destructive;

    /** Freigeben, Veröffentlichen, Übernehmen, Erledigt: Grün. */
    case Confirm;

    /** Erneut versuchen, neu erzeugen, neu verbinden, zurücksetzen, verwerfen: Orange. */
    case Caution;

    /** Umschalter in einem offenen Zustand, der Aufmerksamkeit braucht („Muss geprüft werden“): Gelb. */
    case Warning;

    /** Hauptschritt einer Seite, der nichts anlegt (Einreichen, Verbinden, Speichern): Farbe des Panels. */
    case Primary;

    /** Navigation und Nebenaktionen (Kalender, Drucken, Zurück, Vorlagen, Umschalter): grau. */
    case Secondary;

    /**
     * Gleiche Beschriftung, gleicher Stil, in allen Plattformen (DesignRules::labelStyleViolations). Wer einen Datensatz
     * öffnet, ist View, Navigation und Nebenaktionen sind Secondary, Wieder-Aufnehmen von Ignoriertem ist Caution,
     * Zuordnen und Senden sind der Hauptschritt (Primary).
     *
     * @var array<string, self>
     */
    public const array LABELS = [
        'Ansehen' => self::View,
        'Details' => self::View,
        'Vorschau' => self::View,
        'Öffnen' => self::View,
        'Kunde' => self::View,
        'Postfach' => self::View,
        'Post öffnen' => self::View,
        'Zustellprotokoll' => self::View,
        'Bearbeiten' => self::Edit,
        'Ändern' => self::Edit,
        'Löschen' => self::Delete,
        'Trennen' => self::Destructive,
        'Angebot sperren' => self::Destructive,
        'Link sperren' => self::Destructive,
        'Wiederherstellen' => self::Confirm,
        'Neu erzeugen' => self::Caution,
        'Wieder aufnehmen' => self::Caution,
        'Wieder melden' => self::Caution,
        'Wieder einschalten' => self::Caution,
        'Senden' => self::Primary,
        'Übernehmen' => self::Primary,
        'Zuordnen' => self::Primary,
        'Kunde zuordnen' => self::Primary,
        'Vertrag zuordnen' => self::Primary,
        'In KBM anlegen' => self::Primary,
        'Jetzt prüfen' => self::Secondary,
    ];

    /**
     * Eigene Farbnamen, damit Anlegen, Anzeigen, Bearbeiten und Duplizieren in beiden Panels gleich aussehen und sich von
     * Grün, Rot und Orange abheben (Anlegen und die Panel-Farbe sind beide Teal). In eigenen Ansichten als
     * `color="create"` usw., in CSS als `var(--create-600)`.
     *
     * @var array<string, array<int, string>>
     */
    public const array COLORS = [
        'create' => Color::Teal,
        'view' => Color::Blue,
        'edit' => Color::Indigo,
        'duplicate' => Color::Purple,
        'caution' => Color::Orange,
    ];

    public static function registerColors(): void
    {
        FilamentColor::register(self::COLORS);
    }

    /** Filaments fertige Aktionen im selben Stil wie unsere eigenen. */
    public static function configureBuiltInActions(): void
    {
        $styles = [
            CreateAction::class => self::Create,
            AttachAction::class => self::Create,
            ViewAction::class => self::View,
            EditAction::class => self::Edit,
            ReplicateAction::class => self::Duplicate,
            DeleteAction::class => self::Delete,
            DeleteBulkAction::class => self::Delete,
            ForceDeleteAction::class => self::Delete,
            ForceDeleteBulkAction::class => self::Delete,
            DetachAction::class => self::Destructive,
            DetachBulkAction::class => self::Destructive,
            RestoreAction::class => self::Confirm,
            RestoreBulkAction::class => self::Confirm,
        ];

        foreach ($styles as $class => $style) {
            $class::configureUsing($style(...));
        }
    }

    public function color(): string
    {
        return match ($this) {
            self::Create => 'create',
            self::Primary => 'primary',
            self::View => 'view',
            self::Secondary => 'gray',
            self::Edit => 'edit',
            self::Duplicate => 'duplicate',
            self::Delete, self::Destructive => 'danger',
            self::Confirm => 'success',
            self::Caution => 'caution',
            self::Warning => 'warning',
        };
    }

    /** Festes Icon der Art, `null` = die Aktion bringt ihr eigenes mit. */
    public function icon(): ?Heroicon
    {
        return match ($this) {
            self::Create => Heroicon::OutlinedPlus,
            self::View => Heroicon::OutlinedEye,
            self::Edit => Heroicon::OutlinedPencilSquare,
            self::Duplicate => Heroicon::OutlinedDocumentDuplicate,
            self::Delete => Heroicon::OutlinedTrash,
            default => null,
        };
    }

    /** Aktion in einer Zeile einer eigenen Tabelle, so groß wie in Filament-Tabellen: `->tap(ActionStyle::row(...))`. */
    public static function row(Action $action): Action
    {
        return $action->defaultSize(Size::Small);
    }

    /** Für `$action->tap(ActionStyle::Edit)`. */
    public function __invoke(Action $action): Action
    {
        $action->color($this->color());

        if (($icon = $this->icon()) !== null) {
            $action->icon($icon);
        }

        return $action;
    }
}
