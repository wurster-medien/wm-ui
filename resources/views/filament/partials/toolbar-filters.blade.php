{{-- Filter in der Werkzeugleiste einer Tabelle (App\Filament\Support\ToolbarFilters), wirken sofort. --}}
@foreach ($filters as $filter)
    @switch($filter['type'])
        @case('select')
            {{-- Breite an der Auswahl statt am Feld, sonst bleibt bei langen Beschriftungen kaum Platz für den Wert. --}}
            <x-filament::input.wrapper :prefix="$filter['label']" style="flex-shrink: 0">
                <x-filament::input.select
                    wire:model.live="tableFilters.{{ $filter['name'] }}.value"
                    aria-label="{{ $filter['label'] }}"
                    style="min-width: 7rem; max-width: 14rem"
                >
                    @if ($filter['placeholder'] !== null)
                        <option value=""{{ ($filter['placeholderHidden'] ?? false) ? ' hidden' : '' }}>{{ $filter['placeholder'] }}</option>
                    @endif
                    @foreach ($filter['options'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
            @break

        @case('multiple')
            <x-filament::dropdown placement="bottom-start" width="xs" wire:key="toolbar-filter-{{ $filter['name'] }}">
                <x-slot name="trigger">
                    <x-filament::input.wrapper :prefix="$filter['label']" style="flex-shrink: 0">
                        <button
                            type="button"
                            class="fi-select-input"
                            {{-- Filament stylt .fi-select-input nur an <select>, Abstände deshalb wie dort von Hand. --}}
                            style="width: 100%; min-width: 7rem; max-width: 14rem; padding: 0.375rem 2rem 0.375rem 0.75rem; line-height: 1.5rem; text-align: start; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; background: url(&quot;data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E&quot;) no-repeat right 0.5rem center / 1.5em 1.5em"
                            aria-label="{{ $filter['label'] }}"
                        >
                            {{ $filter['summary'] }}
                        </button>
                    </x-filament::input.wrapper>
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach ($filter['options'] as $value => $label)
                        <label class="fi-dropdown-list-item" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer">
                            <x-filament::input.checkbox
                                wire:model.live="tableFilters.{{ $filter['name'] }}.values"
                                value="{{ $value }}"
                            />
                            <span class="fi-dropdown-list-item-label">{{ $label }}</span>
                        </label>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
            @break


        @case('dates')
            <x-date-range-picker
                :start="'tableFilters.'.$filter['name'].'.'.$filter['fields'][0]['name']"
                :end="'tableFilters.'.$filter['name'].'.'.$filter['fields'][1]['name']"
                live
                :prefix="$filter['label']"
                :label="$filter['label']"
                placeholder="Alle"
                wire:key="toolbar-filter-{{ $filter['name'] }}"
                style="min-width: 14rem"
            />
            @break

        @case('toggle')
            {{-- Im selben Rahmen wie die Auswahlfelder, gleiche Höhe. --}}
            <x-filament::input.wrapper style="flex-shrink: 0">
                <label style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.75rem; line-height: 1.5rem; cursor: pointer; white-space: nowrap">
                    <x-filament::input.checkbox wire:model.live="tableFilters.{{ $filter['name'] }}.isActive" />
                    <span class="fi-fo-field-label-content">{{ $filter['label'] }}</span>
                </label>
            </x-filament::input.wrapper>
            @break
    @endswitch
@endforeach
