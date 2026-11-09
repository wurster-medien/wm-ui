{{--
    Titel einer Zeile in eigenen Listen (Blade), der das Ziel der Zeile öffnet (RowClick, Simon 29.10.2026): Knopf ohne
    Knopf-Optik, der die übergebene Aktion mit ihren Argumenten öffnet; darf man sie nicht, bleibt nur der Text. Einen
    Knopf derselben Aktion gibt es in der Zeile dann nicht mehr. Die ganze Zeile (Tabellenzeile bzw. Element mit
    data-wm-row) öffnet dasselbe Ziel (wm-ui.js, Simon 9.11.2026).

    @var \Filament\Actions\Action $action  z. B. ($this->editAliasAction)(['id' => …])
--}}
@props(['action'])
@if ($action->isVisible())
    <button type="button" {{ $attributes->class('wm-row-open') }} wire:click="mountAction(@js($action->getName()), @js($action->getArguments()))">{{ $slot }}</button>
@else
    <span {{ $attributes }}>{{ $slot }}</span>
@endif
