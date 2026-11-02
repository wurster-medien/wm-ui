{{-- WursterMedien\Ui\Forms\DateRangePicker: Beschriftung und Fehler wie ein Filament-Feld, darin <x-date-range-picker>. --}}
@php
    $startField = $schemaComponent->getStartField();
    $endField = $schemaComponent->getEndField();
    $startPath = $startField->getStatePath();
    $endPath = $endField->getStatePath();
    $errorMessages = array_values(array_unique([...$errors->get($startPath), ...$errors->get($endPath)]));
    $isDisabled = $schemaComponent->isDisabled();
@endphp

<div
    {{
        $attributes
            ->merge(['id' => $getId()], escape: false)
            ->merge($getExtraAttributes(), escape: false)
    }}
>
    <x-filament-forms::field-wrapper
        :field="$startField"
        :label="$getLabel()"
        :required="$schemaComponent->isRequired()"
        :error-messages="filled($errorMessages) ? $errorMessages : null"
        :has-errors="filled($errorMessages)"
    >
        <x-date-range-picker
            :start="$startPath"
            :end="$endPath"
            :live="$startField->isLive()"
            :min="$schemaComponent->getMinDate()"
            :max="$schemaComponent->getMaxDate()"
            :clearable="! $schemaComponent->isRequired()"
            :end-optional="$schemaComponent->isEndOptional()"
            :disabled="$isDisabled"
            :valid="blank($errorMessages)"
            :id="$startField->getId()"
            :label="$getLabel()"
        />
    </x-filament-forms::field-wrapper>
</div>
