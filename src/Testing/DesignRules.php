<?php

namespace WursterMedien\Ui\Testing;

use Symfony\Component\Finder\Finder;
use WursterMedien\Ui\ActionStyle;

/**
 * Regeln des gemeinsamen Designs als Prüfung über den Quelltext, in jeder Plattform aufgerufen, damit neue Abweichungen
 * dort einen Test scheitern lassen.
 */
final class DesignRules
{
    /**
     * Tabellen mit Zeilenaktionen müssen über RowClick entscheiden, was die Zeile öffnet, und dürfen recordUrl() bzw.
     * recordAction() nicht direkt setzen.
     *
     * @return list<string>
     */
    public static function rowClickViolations(string $filamentPath): array
    {
        $problems = [];

        foreach (Finder::create()->files()->in($filamentPath)->name('*.php') as $file) {
            $source = $file->getContents();
            $path = str_replace('\\', '/', $file->getRelativePathname());

            if (str_contains($source, '->recordActions(') && ! str_contains($source, 'RowClick::')) {
                $problems[] = "{$path}: Tabelle mit Zeilenaktionen ohne RowClick::opens|url|none";
            }

            if (preg_match('/->record(Url|Action)\(/', $source) === 1) {
                $problems[] = "{$path}: recordUrl()/recordAction() nur über RowClick";
            }
        }

        sort($problems);

        return $problems;
    }

    /**
     * Gleiche Beschriftung, gleicher Stil: Beschriftungen aus ActionStyle::LABELS tragen überall ihren festen Stil.
     *
     * @return list<string>
     */
    public static function labelStyleViolations(string $appPath): array
    {
        $problems = [];

        foreach (Finder::create()->files()->in($appPath)->name('*.php') as $file) {
            $source = $file->getContents();
            $path = str_replace('\\', '/', $file->getRelativePathname());

            preg_match_all('/\b\w*Action::make\(/', $source, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as [$call, $offset]) {
                $chain = substr($source, $offset + strlen($call));
                $next = strpos($chain, '::make(');
                $chain = substr($chain, 0, $next === false ? 1500 : $next);

                if (preg_match("/->label\('([^']+)'\)/", $chain, $label) !== 1 || ! isset(ActionStyle::LABELS[$label[1]])) {
                    continue;
                }

                $expected = ActionStyle::LABELS[$label[1]];

                if (preg_match_all('/ActionStyle::(?!row)(\w+)/', $chain, $styles) > 0 && ! in_array($expected->name, $styles[1], true)) {
                    $line = substr_count(substr($source, 0, $offset), "\n") + 1;
                    $problems[] = "{$path}:{$line}: „{$label[1]}“ gehört zu ActionStyle::{$expected->name}";
                }
            }
        }

        sort($problems);

        return $problems;
    }

    /**
     * Ratsche gegen neue eigene Stile in Ansichten: Blade-Dateien mit einem style-Element, die nicht in der Erlaubnisliste
     * stehen (Pfade relativ zu $viewsPath, mit Schrägstrichen). Stile gehören nach resources/dist/wm-ui.css.
     *
     * @param  list<string>  $allowlist
     * @return list<string>
     */
    public static function inlineStyleViolations(string $viewsPath, array $allowlist = []): array
    {
        $problems = [];

        foreach (Finder::create()->files()->in($viewsPath)->name('*.blade.php') as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());

            if (preg_match('/<style[\s>]/i', $file->getContents()) === 1 && ! in_array($path, $allowlist, true)) {
                $problems[] = "{$path}: eigenes style-Element, Stil gehört ins Paket (resources/dist/wm-ui.css)";
            }
        }

        sort($problems);

        return $problems;
    }
}
