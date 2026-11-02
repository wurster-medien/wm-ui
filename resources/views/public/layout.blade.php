{{--
    Layout aller öffentlichen Seiten ohne Login auf allen Plattformen, Farben von wurster medien (Teal). Hier stehen die Farben als Tokens (hell und dunkel), der dunkle Kopf mit
    Logo und Seitenkopf (Simon, 1.11.2026, Entwurf A; beginnt die Seite mit einer Karte, ragt sie in den Kopf), die
    Fußzeile mit den Rechtslinks und die gemeinsamen Bausteine (Karte, Hinweis, Formular, Häkchen, Liste).
    Seiten nutzen dazu <x-public.header>, <x-public.button>, <x-public.notice> und <x-public.icon> und schreiben keine
    eigenen Farben (PublicPagesTest). Eigene Styles einer Seite (@push('head')) nur für ihren Aufbau, mit den Tokens.

    Abschnitte: title, main_class (wide = breite Seite), customer (Firma rechts neben dem Logo), brand_actions (rechts
    neben dem Logo), footer (Hinweis über den Rechtslinks).

    Hell oder dunkel: Ohne Wahl folgt die Seite dem System. Der Umschalter oben rechts (<x-public.theme-switch>) setzt
    data-theme auf <html> und merkt sich die Wahl wie die Panels unter localStorage "theme" (light, dark, system), sie
    gilt also auch im Portal und im Admin. Das Skript im Kopf setzt sie vor dem Zeichnen, damit nichts aufblitzt.
--}}
@php
    $darkTokens = '
                --bg: #0f0f11; --card: #1a1a1d; --text: #f4f4f5; --muted: #a1a1aa; --border: #2e2e33; --soft: #222226;
                --tint: rgba(20, 184, 166, .13); --tint-text: #2dd4bf; --link: #2dd4bf;
                --danger: #ef4444; --danger-tint: rgba(239, 68, 68, .12); --danger-text: #f87171;
                --info: #3b82f6; --info-tint: rgba(59, 130, 246, .12); --info-text: #60a5fa;
                --warning: #f59e0b; --warning-tint: rgba(245, 158, 11, .12); --warning-text: #fbbf24;
                --hero: #1a1a1d; --hero-text: #f4f4f5; --hero-muted: #a1a1aa; --hero-line: #2e2e33; --hero-hover: rgba(255, 255, 255, .08); --hero-accent: #2dd4bf;
                --strong: #f4f4f5; --strong-text: #111827;
                --shadow: none; color-scheme: dark;
    ';
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            try {
                var theme = window.localStorage.getItem('theme');
                if (theme === 'light' || theme === 'dark') { document.documentElement.dataset.theme = theme; }
            } catch (error) {}
        })();
    </script>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#242D33">
    <title>@yield('title', config('wm-ui.name')) · {{ config('wm-ui.owner') }}</title>
    {{-- Zeichen der Plattform wie in den Panels. --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            --bg: #eef0f2; --card: #ffffff; --text: #111827; --muted: #6b7280; --border: #e5e7eb; --soft: #f9fafb;
            --primary: #0d9488; --primary-text: #ffffff; --tint: #f0fdfa; --tint-text: #0f766e; --link: #0f766e; --brand: #14b8a6; --button: #0c8074;
            --danger: #dc2626; --danger-tint: #fef2f2; --danger-text: #b91c1c;
            --info: #2563eb; --info-tint: #eff6ff; --info-text: #1d4ed8;
            --warning: #d97706; --warning-tint: #fffbeb; --warning-text: #b45309;
            --paper: #ffffff; --paper-text: #111827; --paper-muted: #6b7280;
            --hero: #242D33; --hero-text: #ffffff; --hero-muted: #d1d9de; --hero-line: rgba(255, 255, 255, .16); --hero-hover: rgba(255, 255, 255, .16); --hero-accent: #5eead4;
            --strong: #242D33; --strong-text: #ffffff;
            --shadow: 0 1px 2px rgba(17, 24, 39, .04), 0 8px 24px rgba(17, 24, 39, .05);
            color-scheme: light;
        }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) { {!! $darkTokens !!} }
        }
        :root[data-theme="dark"] { {!! $darkTokens !!} }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--bg); color: var(--text); font: 16px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; -webkit-font-smoothing: antialiased; }
        main { position: relative; max-width: 892px; margin: 0 auto; padding: 24px 16px 48px; }
        main.wide { max-width: 1180px; }
        main:has(> .card:first-child) { margin-top: -80px; padding-top: 0; }
        main > .card:first-child { margin-top: 0; box-shadow: 0 12px 32px rgba(17, 24, 39, .12); }
        main > .card.accent:first-child { background: linear-gradient(var(--tint), var(--tint)), var(--card); }
        a { color: var(--link); overflow-wrap: anywhere; text-underline-offset: 2px; }
        h1, h2, h3 { letter-spacing: -.01em; }
        h2 { font-size: 1.1rem; margin: 0 0 8px; }
        h3 { font-size: 1rem; margin: 0 0 6px; }

        /* Dunkler Kopf mit Logo und Seitenkopf */
        .pub-top { padding: 24px 16px 40px; background: var(--hero); color: var(--hero-text); }
        .pub-top:has(+ main > .card:first-child) { padding-bottom: 112px; }
        .pub-top-inner { max-width: 860px; margin: 0 auto; }
        .pub-top-inner.wide { max-width: 1148px; }
        .pub-brandbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .pub-brand { display: inline-flex; align-items: center; }
        .pub-brand img { height: 34px; width: auto; display: block; }
        .pub-brand .pub-brand-print { display: none; }
        .pub-brand-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px 16px; }
        .pub-customer { color: var(--hero-muted); font-size: .95rem; text-align: right; overflow-wrap: anywhere; }

        /* Umschalter hell, dunkel, System (Komponente public.theme-switch) */
        .pub-theme { display: inline-flex; gap: 2px; padding: 3px; border: 1px solid var(--hero-line); border-radius: 10px; }
        .pub-theme button { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 30px; margin: 0; padding: 0; border: 0; border-radius: 7px; background: transparent; color: var(--hero-muted); cursor: pointer; transition: background-color .15s, color .15s; }
        .pub-theme button:hover { color: var(--hero-text); }
        .pub-theme button[aria-pressed="true"] { background: var(--hero-hover); color: var(--hero-text); }
        .pub-theme button:focus-visible { outline: 2px solid var(--hero-accent); outline-offset: 1px; }
        .pub-theme .pub-icon { width: 18px; height: 18px; }

        /* Seitenkopf (Komponente public.header) */
        .pub-head { margin-top: 44px; }
        .pub-kicker { margin: 0 0 8px; color: var(--hero-accent); font-size: .85rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .pub-head h1 { font-size: clamp(2rem, 5.5vw, 3rem); font-weight: 700; letter-spacing: -.025em; line-height: 1.1; margin: 0; color: var(--hero-text); overflow-wrap: anywhere; }
        .pub-sub { margin: 10px 0 0; color: var(--hero-muted); font-size: 1.05rem; overflow-wrap: anywhere; }
        .pub-intro { margin: 12px 0 0; max-width: 44rem; color: var(--hero-muted); font-size: 1.1rem; }
        .pub-intro > p { margin: 0; }
        .pub-intro > p + p { margin-top: 8px; }
        .pub-intro a { color: var(--hero-accent); }

        /* Karten, Hinweise, Kennzeichen */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 32px; margin-top: 16px; box-shadow: var(--shadow); min-width: 0; }
        @media (max-width: 640px) { .card { padding: 18px; } }
        .card.accent { background: var(--tint); border-color: transparent; box-shadow: none; }
        .card > :first-child { margin-top: 0; }
        .card > :last-child { margin-bottom: 0; }
        .card-title { display: flex; align-items: center; gap: 10px; margin: 0 0 8px; font-size: 1.05rem; }
        .card-title .pub-icon { color: var(--tint-text); }
        .card.accent .card-title { color: var(--tint-text); }
        .meta { color: var(--muted); font-size: .9rem; }
        .notice { padding: 12px 16px; border-radius: 12px; margin-top: 16px; border: 1px solid var(--border); background: var(--card); overflow-wrap: anywhere; }
        .notice > :first-child { margin-top: 0; }
        .notice > :last-child { margin-bottom: 0; }
        .notice.success { background: var(--tint); border-color: color-mix(in srgb, var(--primary) 30%, transparent); }
        .notice.success strong { color: var(--tint-text); }
        .notice.error { background: var(--danger-tint); border-color: color-mix(in srgb, var(--danger) 35%, transparent); }
        .notice.error strong { color: var(--danger-text); }
        .notice.info { background: var(--info-tint); border-color: color-mix(in srgb, var(--info) 30%, transparent); }
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 2px 10px; border-radius: 999px; font-size: .8rem; font-weight: 600; background: var(--soft); color: var(--muted); border: 1px solid var(--border); white-space: nowrap; }
        .badge.accent { background: var(--tint); color: var(--tint-text); border-color: transparent; }
        .badge.warning { background: var(--warning-tint); color: var(--warning-text); border-color: transparent; }
        .badge.danger { background: var(--danger-tint); color: var(--danger-text); border-color: transparent; }
        .pub-num { flex: none; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 999px; background: var(--primary); color: var(--primary-text); font-size: .85rem; font-weight: 700; }
        .pub-icon { width: 18px; height: 18px; flex: none; }

        /* Listen: Punkte mit Häkchen (Hinweise) bzw. schlichte Aufzählung */
        .pub-points { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
        .pub-points li { position: relative; padding-left: 32px; }
        .pub-points li::before { content: ""; position: absolute; left: 0; top: 1px; width: 22px; height: 22px; border-radius: 999px; background: var(--tint); }
        .pub-points li::after { content: ""; position: absolute; left: 8px; top: 6px; width: 5px; height: 10px; border: solid var(--tint-text); border-width: 0 2px 2px 0; transform: rotate(45deg); }
        .pub-bullets { margin: 8px 0 0; padding-left: 20px; }
        .pub-bullets li + li { margin-top: 4px; }
        .pub-bullets li::marker { color: var(--muted); }

        /* Buttons (Komponente public.button): Hauptaktion kräftig in Teal mit weißer Schrift wie in den Mails, die übrigen „soft“ wie in den Panels */
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px; }
        .actions > .pub-btn { flex: 1 1 200px; }
        .pub-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; margin: 0; padding: 10px 18px; border: 1px solid transparent; border-radius: 12px; font: inherit; font-weight: 600; line-height: 1.25; text-align: center; text-decoration: none; overflow-wrap: normal; cursor: pointer; transition: background-color .15s, border-color .15s; }
        .pub-btn-primary { background: var(--button); color: #ffffff; }
        .pub-btn-primary:hover { background: #0f766e; }
        .pub-btn-dark { background: var(--strong); color: var(--strong-text); }
        .pub-btn-dark:hover { background: color-mix(in srgb, var(--strong) 85%, var(--card)); }
        .pub-btn-danger { background: color-mix(in srgb, var(--danger) 9%, var(--card)); color: var(--danger-text); border-color: color-mix(in srgb, var(--danger) 40%, transparent); }
        .pub-btn-danger:hover { background: color-mix(in srgb, var(--danger) 16%, var(--card)); }
        .pub-btn-warning { background: color-mix(in srgb, var(--warning) 10%, var(--card)); color: var(--warning-text); border-color: color-mix(in srgb, var(--warning) 45%, transparent); }
        .pub-btn-warning:hover { background: color-mix(in srgb, var(--warning) 18%, var(--card)); }
        .pub-btn-secondary { background: var(--card); color: var(--text); border-color: var(--border); }
        .pub-btn-secondary:hover { border-color: var(--muted); }
        .pub-btn-sm { min-height: 38px; padding: 7px 14px; border-radius: 10px; font-size: .9rem; }
        .pub-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
        .pub-btn:disabled { opacity: .6; cursor: progress; }

        /* Formulare (ohne Spezifität, damit Oberflächen wie der Material-Upload eigene Felder behalten) */
        :where(label) { display: block; font-weight: 600; margin: 14px 0 4px; font-size: .95rem; }
        :where(input:not([type="checkbox"], [type="radio"], [type="file"], [type="hidden"]), textarea, select) { display: block; width: 100%; min-height: 46px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font: inherit; background: var(--card); color: var(--text); }
        :where(select) { appearance: none; -webkit-appearance: none; padding-right: 2rem; background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E"); background-position: right .5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; }
        :where(input:not([type="checkbox"], [type="radio"]), textarea, select):focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); }
        :where(textarea) { min-height: 110px; resize: vertical; }
        .error-text { color: var(--danger-text); font-size: .9rem; margin: 4px 0 0; }
        .hint-text { color: var(--muted); font-size: .9rem; margin: 4px 0 0; }
        .pub-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
        @media (max-width: 640px) { .pub-row { grid-template-columns: 1fr; } }

        /* Häkchen: <label class="pub-check"><input type="checkbox"> <span>…</span></label> */
        .pub-check { display: flex; gap: 12px; align-items: flex-start; margin: 0; padding: 10px 0; font-weight: 400; cursor: pointer; }
        .pub-check input[type="checkbox"] { appearance: none; -webkit-appearance: none; flex: none; width: 22px; height: 22px; margin: 1px 0 0; padding: 0; border: 2px solid var(--border); border-radius: 7px; background: var(--card); cursor: pointer; transition: background-color .15s, border-color .15s; }
        .pub-check input[type="checkbox"]:hover:not(:disabled) { border-color: var(--primary); }
        .pub-check input[type="checkbox"]:checked { border-color: var(--primary); background: var(--primary) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m5 12.5 4.5 4.5L19 7'/%3E%3C/svg%3E") center / 16px no-repeat; }
        .pub-check input[type="checkbox"]:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
        .pub-check input[type="checkbox"]:disabled { cursor: default; opacity: .7; }
        .pub-check > span { min-width: 0; }

        /* Mehrzeiliger Fremdtext (Kommentar, Bildunterschrift) mit Zeilenumbrüchen */
        .caption { white-space: pre-wrap; overflow-wrap: anywhere; margin: 12px 0 0; }

        /* Dialog (<dialog class="pub-dialog">, geöffnet per showModal), Seite dahinter scrollt nicht mit */
        .pub-dialog { width: min(1080px, calc(100vw - 32px)); max-height: calc(100dvh - 32px); padding: 0; border: 1px solid var(--border); border-radius: 18px; background: var(--card); color: var(--text); box-shadow: 0 24px 64px rgba(17, 24, 39, .28); }
        .pub-dialog::backdrop { background: rgba(17, 24, 39, .6); }
        html:has(.pub-dialog[open]) { overflow: hidden; }
        @media (max-width: 640px) { .pub-dialog { width: 100vw; max-width: 100vw; max-height: 100dvh; height: 100dvh; margin: 0; border-radius: 0; border: 0; } }

        /* Leerer Zustand und Fußzeile */
        .pub-empty { padding: 40px 20px; text-align: center; color: var(--muted); }
        .pub-empty .pub-icon { width: 36px; height: 36px; color: var(--tint-text); margin-bottom: 8px; }
        .pub-footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid var(--border); color: var(--muted); font-size: .85rem; }
        .pub-footer p { margin: 0 0 8px; }
        .pub-footer a { color: var(--muted); }
        .pub-footer a:hover { color: var(--text); }

        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } * { transition: none !important; } }
        @media print {
            body { background: #fff; color: #000; }
            main, main.wide { max-width: none; margin-top: 0; padding: 0; }
            .pub-top, .pub-top:has(+ main > .card:first-child) { padding: 0 0 16px; background: none; color: #000; }
            .pub-brand .pub-brand-screen { display: none; }
            .pub-brand .pub-brand-print { display: block; }
            .pub-head { margin-top: 16px; }
            .pub-head h1, .pub-kicker, .pub-sub, .pub-intro { color: #000; }
            .no-print, .pub-footer { display: none !important; }
            .card { box-shadow: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    @stack('head')
</head>
<body>
<div class="pub-top">
    <div class="pub-top-inner @yield('main_class')">
        <div class="pub-brandbar">
            @include('public.partials.brand')
            <div class="pub-brand-actions">
                @hasSection('customer')
                    <span class="pub-customer">@yield('customer')</span>
                @endif
                <div class="pub-brand-actions no-print">
                    @hasSection('brand_actions')
                        <div>@yield('brand_actions')</div>
                    @endif
                    <x-public.theme-switch />
                </div>
            </div>
        </div>
        @stack('hero')
    </div>
</div>
<main class="@yield('main_class')">
    @yield('content')

    <footer class="pub-footer">
        @hasSection('footer')
            <p>@yield('footer')</p>
        @endif
        <nav aria-label="Rechtliches">
            {{-- Je Plattform eigene Links. --}}
            @include('public.partials.legal-links')
        </nav>
    </footer>
</main>
@stack('scripts')
{{-- Umschalter hell, dunkel, System: wie die Panels in localStorage "theme", ohne Speicher wirkt er nur auf dieser Seite. --}}
<script>
    (function () {
        var buttons = document.querySelectorAll('[data-theme-choice]');
        function current() {
            var theme = document.documentElement.dataset.theme;
            return theme === 'light' || theme === 'dark' ? theme : 'system';
        }
        function show() {
            buttons.forEach(function (button) { button.setAttribute('aria-pressed', button.dataset.themeChoice === current() ? 'true' : 'false'); });
        }
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var choice = button.dataset.themeChoice;
                if (choice === 'system') { delete document.documentElement.dataset.theme; } else { document.documentElement.dataset.theme = choice; }
                try { window.localStorage.setItem('theme', choice); } catch (error) {}
                show();
            });
        });
        show();
    })();
</script>
{{-- Formulare mit data-submit-once gehen nur einmal ab (Doppelklick). Die Buttons werden erst nach dem Absenden
     gesperrt, damit der geklickte Button (name="decision") mitgeschickt wird. --}}
<script>
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.hasAttribute('data-submit-once')) { return; }
        if (form.dataset.submitted) { event.preventDefault(); return; }
        form.dataset.submitted = '1';
        setTimeout(function () {
            form.querySelectorAll('button').forEach(function (button) { button.disabled = true; });
        }, 0);
    });
    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) { return; }
        document.querySelectorAll('form[data-submit-once]').forEach(function (form) {
            delete form.dataset.submitted;
            form.querySelectorAll('button').forEach(function (button) { button.disabled = false; });
        });
    });
</script>
</body>
</html>
