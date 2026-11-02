{{--
    „Sortieren nach“ in der Werkzeugleiste für Tabellen ohne Spaltenköpfe (App\Filament\Support\ToolbarFilters), Verhalten
    wie Filaments eigene Auswahl über dem Inhalt (schreibt `tableSort`, Richtung erst nach Wahl einer Spalte).

    @var list<array{name: string, label: string}> $columns
    @var string|\Illuminate\Contracts\Support\Htmlable|null $defaultLabel
--}}
<div
    x-data="{
        sort: $wire.$entangle('tableSort', true),
        column: null,
        direction: null,
    }"
    x-init="
        if (sort) {
            ;[column, direction] = sort.split(':')
            direction ??= 'asc'
        }

        $watch('sort', function () {
            if (! sort) {
                return
            }

            ;[column, direction] = sort.split(':')
            direction ??= 'asc'
        })

        $watch('direction', function () {
            sort = column ? `${column}:${direction}` : null
        })

        $watch('column', function (newColumn, oldColumn) {
            if (! newColumn) {
                direction = null
                sort = null

                return
            }

            if (oldColumn) {
                sort = `${column}:${direction}`

                return
            }

            direction = 'asc'
            sort = `${column}:${direction}`
        })
    "
    class="fi-ta-sorting-settings wm-toolbar-sorting"
    style="display: flex; gap: 0.5rem"
>
    <label>
        <x-filament::input.wrapper :prefix="__('filament-tables::table.sorting.fields.column.label')">
            <x-filament::input.select x-model="column">
                <option value="">{{ $defaultLabel }}</option>
                @foreach ($columns as $column)
                    <option value="{{ $column['name'] }}">{{ $column['label'] }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </label>

    <label x-cloak x-show="column">
        <span class="fi-sr-only">{{ __('filament-tables::table.sorting.fields.direction.label') }}</span>
        <x-filament::input.wrapper>
            <x-filament::input.select x-model="direction">
                <option value="asc">{{ __('filament-tables::table.sorting.fields.direction.options.asc') }}</option>
                <option value="desc">{{ __('filament-tables::table.sorting.fields.direction.options.desc') }}</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </label>
</div>
