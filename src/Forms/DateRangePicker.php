<?php

namespace WursterMedien\Ui\Forms;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Concerns\HasLabel;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Zeitraum (von–bis) als ein Feld mit einem Kalender (<x-date-range-picker>), gespeichert in zwei Werten wie bisher,
 * z. B. DateRangePicker::make('starts_on', 'ends_on'). Die beiden Werte sind unsichtbare DatePicker im Schema: Sie
 * prüfen (Pflicht, Grenzen, Ende nicht vor Beginn), wandeln Datumswerte in Y-m-d und gehen in die Formulardaten ein,
 * gezeichnet wird nur der Zeitraum. Fehler beider Werte stehen unter dem Feld.
 */
class DateRangePicker extends Component
{
    use HasLabel;

    public const END_BEFORE_START = 'Das Ende darf nicht vor dem Beginn liegen.';

    protected string $view = 'filament.forms.date-range-picker';

    protected DatePicker $startField;

    protected DatePicker $endField;

    protected bool|Closure $isRequired = false;

    protected bool $isEndOptional = false;

    protected string|Closure|null $minDate = null;

    protected string|Closure|null $maxDate = null;

    final public function __construct(string $start, string $end)
    {
        $this->startField = DatePicker::make($start)
            ->label('Beginn')
            ->required(fn (): bool => $this->isRequired())
            ->minDate(fn (): ?string => $this->getMinDate())
            ->maxDate(fn (): ?string => $this->getMaxDate());

        $this->endField = DatePicker::make($end)
            ->label('Ende')
            ->required(fn (): bool => $this->isRequired() && ! $this->isEndOptional)
            ->minDate(fn (): ?string => $this->getMinDate())
            ->maxDate(fn (): ?string => $this->getMaxDate())
            ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $start): void {
                $from = $get($start);

                if (filled($value) && filled($from) && substr((string) $value, 0, 10) < substr((string) $from, 0, 10)) {
                    $fail(self::END_BEFORE_START);
                }
            });

        $this->schema([$this->startField, $this->endField]);
    }

    public static function make(string $start, string $end): static
    {
        $static = app(static::class, ['start' => $start, 'end' => $end]);
        $static->configure();

        return $static;
    }

    public function required(bool|Closure $condition = true): static
    {
        $this->isRequired = $condition;

        return $this;
    }

    public function isRequired(): bool
    {
        return (bool) $this->evaluate($this->isRequired);
    }

    /** Das Ende darf fehlen („ab 01.11.2026“), auch wenn der Zeitraum Pflicht ist. */
    public function endOptional(bool $condition = true): static
    {
        $this->isEndOptional = $condition;

        return $this;
    }

    public function isEndOptional(): bool
    {
        return $this->isEndOptional;
    }

    /** Beide Werte live (z. B. für eine Vorschau, die vom Zeitraum abhängt). */
    public function live(bool $onBlur = false, string|int|null $debounce = null, bool|Closure|null $condition = true): static
    {
        $this->startField->live(condition: $condition);
        $this->endField->live(condition: $condition);

        return $this;
    }

    /** Namen der Werte in Fehlermeldungen (Standard „Beginn“ und „Ende“). */
    public function partLabels(string $start, string $end): static
    {
        $this->startField->label($start);
        $this->endField->label($end);

        return $this;
    }

    public function helperText(string|Closure|null $text): static
    {
        $this->startField->helperText($text);

        return $this;
    }

    public function minDate(string|Closure|null $date): static
    {
        $this->minDate = $date;

        return $this;
    }

    public function maxDate(string|Closure|null $date): static
    {
        $this->maxDate = $date;

        return $this;
    }

    public function getMinDate(): ?string
    {
        return $this->evaluate($this->minDate);
    }

    public function getMaxDate(): ?string
    {
        return $this->evaluate($this->maxDate);
    }

    /** Der Wert für den Beginn, mit seinem Platz im Schema (Zustandspfad). */
    public function getStartField(): DatePicker
    {
        $this->getChildSchema()->getComponents(withHidden: true);

        return $this->startField;
    }

    public function getEndField(): DatePicker
    {
        $this->getChildSchema()->getComponents(withHidden: true);

        return $this->endField;
    }
}
