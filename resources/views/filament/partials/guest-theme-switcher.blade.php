{{--
    Hell/Dunkel/System-Umschalter für Seiten ohne Anmeldung (Login, Passwort vergessen). Angemeldet steht derselbe
    Umschalter im Nutzermenü. Filaments Knöpfe rufen close() eines umgebenden Dropdowns auf, hier ein leeres.
    Eingebunden über den Render-Hook PanelsRenderHook::SIMPLE_LAYOUT_START (PanelDefaults).
--}}
@unless (filament()->auth()->check())
    <div
        x-data="{ close() {} }"
        class="wm-guest-theme-switcher"
    >
        <x-filament-panels::theme-switcher />
    </div>

    <style>
        .wm-guest-theme-switcher {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 10;
            padding: 0.25rem;
            border-radius: 0.5rem;
            background: #fff;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05), 0 0 0 1px rgb(3 7 18 / 0.05);
        }

        .dark .wm-guest-theme-switcher {
            background: var(--gray-900);
            box-shadow: 0 0 0 1px rgb(255 255 255 / 0.1);
        }
    </style>
@endunless
