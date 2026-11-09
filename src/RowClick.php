<?php

namespace WursterMedien\Ui;

use Closure;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Klick auf die Zeile, ein Konzept für alle Tabellen in Admin und Portal (Simon, 29.10.2026: „einheitlich Konzept für
 * alles“):
 *
 * - Hat der Datensatz etwas zum Öffnen, öffnet es die ganze Zeile: zuerst seine eigene Seite bzw. „Anzeigen“/„Details“,
 *   sonst „Bearbeiten“ (Seite oder Fenster), wenn man darf. Sonst ist die Zeile nicht klickbar.
 * - Was die Zeile öffnet, steht nicht noch einmal als Knopf in ihr: der Knopf bleibt als Aktion, ist aber unsichtbar
 *   (TARGET_CLASS). Knöpfe mit anderem Ziel bleiben (z. B. „Bearbeiten“ in einer Zeile, die die Seite öffnet).
 * - Links im Inhalt (Kunde, Postfach …) und Knöpfe gehen dem Klick auf die Zeile vor.
 * - Aufgaben-Listen öffnen nur, was zu ihrem Datensatz gehört (Details, Bearbeiten), nie den Hauptschritt
 *   (Übernehmen, Senden, Erledigt …); der bleibt Knopf.
 * - Nie ein Klick auf die Zeile, der etwas auslöst, zählt oder Geheimnisse zeigt (Passwort anzeigen, Senden, Löschen).
 *
 * Jede Filament-Tabelle mit Zeilenaktionen entscheidet über opens(), url() oder none(), nie über recordUrl() bzw.
 * recordAction() direkt (RowClickTest). Eigene Listen (Blade) machen den Titel über `<x-row-open :action="…">` zum
 * Auslöser desselben Ziels und lassen dessen Knopf weg (Kasten „To-Dos“, DNS-Werte, Weiterleitungen, Abwesenheiten);
 * die ganze Tabellenzeile bzw. das Element mit `data-wm-row` darum öffnet es ebenso (wm-ui.js, Simon 9.11.2026).
 */
final class RowClick
{
    /** Klasse am Knopf, den der Klick auf die Zeile ersetzt (filament.partials.table-styles). */
    public const string TARGET_CLASS = 'wm-row-click';

    /** Klasse an der Aktion, die am Handy unsichtbar über der ganzen Karte liegt (wm-ui.css). */
    public const string PHONE_TARGET_CLASS = 'wm-row-phone-open';

    /** Klasse an Knöpfen, die am Handy nur im Fenster der Karte stehen (wm-ui.css). */
    public const string PHONE_HIDDEN_CLASS = 'wm-row-not-on-phone';

    /**
     * Die erste sichtbare der genannten Zeilenaktionen öffnet die Zeile (mit Adresse als Link, sonst ihr Fenster), ihr
     * Knopf entfällt: `->tap(RowClick::opens('view', 'edit'))` nach recordActions().
     *
     * @return Closure(Table): Table
     */
    public static function opens(string ...$actions): Closure
    {
        return function (Table $table) use ($actions): Table {
            foreach ($actions as $name) {
                $table->getAction($name)?->extraAttributes(
                    fn (Model|array|null $record = null): array => $record !== null && self::target($table, $record, $actions)?->getName() === $name ? ['class' => self::TARGET_CLASS] : [],
                    merge: true,
                );
            }

            return $table
                ->recordUrl(fn (Model|array $record): ?string => self::target($table, $record, $actions)?->getUrl())
                ->openRecordUrlInNewTab(fn (Model|array $record): bool => self::target($table, $record, $actions)?->shouldOpenUrlInNewTab() ?? false)
                ->recordAction(fn (Model|array $record): ?string => ($action = self::target($table, $record, $actions)) !== null && $action->getUrl() === null ? $action->getName() : null);
        };
    }

    /**
     * Am Handy öffnet die ganze Karte einer Tabelle mit ->stackedOnMobile() diese Aktion statt des Ziels von opens(), am
     * Rechner gilt weiter opens(). Die Aktion liegt nur per CSS über der Karte, der Server muss die Breite nicht kennen.
     * Die übrigen genannten Knöpfe stehen am Handy nicht auf der Karte, die Aktion bietet sie in ihrem Fenster an:
     * `->tap(RowClick::opensOnPhone('view', 'delete'))` nach recordActions().
     *
     * @return Closure(Table): Table
     */
    public static function opensOnPhone(string $action, string ...$onlyInWindow): Closure
    {
        return function (Table $table) use ($action, $onlyInWindow): Table {
            $table->getAction($action)?->extraAttributes(['class' => self::PHONE_TARGET_CLASS], merge: true);

            foreach ($onlyInWindow as $name) {
                $table->getAction($name)?->extraAttributes(['class' => self::PHONE_HIDDEN_CLASS], merge: true);
            }

            return $table;
        };
    }

    /**
     * Die Zeile öffnet die eigene Seite des Datensatzes, ohne Knopf dafür: `->tap(RowClick::url(fn (Customer $record) => …))`.
     *
     * @return Closure(Table): Table
     */
    public static function url(Closure $url, bool $newTab = false): Closure
    {
        return fn (Table $table): Table => $table->recordUrl($url, $newTab)->recordAction(null);
    }

    /**
     * Die Zeile öffnet nichts (es gibt nichts zu öffnen, oder jedes Ziel löst etwas aus).
     *
     * @return Closure(Table): Table
     */
    public static function none(): Closure
    {
        return fn (Table $table): Table => $table->recordUrl(null)->recordAction(null);
    }

    /**
     * Erste sichtbare der Aktionen für diesen Datensatz (wie Filament in ListRecords, auch in „Mehr“).
     *
     * @param  Model|array<string, mixed>  $record
     * @param  array<string>  $names
     */
    private static function target(Table $table, Model|array $record, array $names): ?Action
    {
        foreach ($names as $name) {
            $action = $table->getAction($name);

            if ($action === null) {
                continue;
            }

            $action = clone $action;
            $action->record($record);
            $group = $action->getGroup();

            while ($group !== null) {
                $group->record($record);
                $group = $group->getGroup();
            }

            if (! $action->isHidden()) {
                return $action;
            }
        }

        return null;
    }
}
