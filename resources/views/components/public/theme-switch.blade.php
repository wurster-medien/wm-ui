{{--
    Umschalter hell, dunkel, System für alle öffentlichen Seiten, steht im Kopf von public.layout (dort die Styles
    .pub-theme und das Skript). Die Wahl gilt wie in den Panels (localStorage "theme").
--}}
<div class="pub-theme" role="group" aria-label="Darstellung">
    <button type="button" data-theme-choice="light" aria-pressed="false" title="Hell" aria-label="Hell"><x-public.icon name="sun" /></button>
    <button type="button" data-theme-choice="dark" aria-pressed="false" title="Dunkel" aria-label="Dunkel"><x-public.icon name="moon" /></button>
    <button type="button" data-theme-choice="system" aria-pressed="false" title="Wie das System" aria-label="Wie das System"><x-public.icon name="computer" /></button>
</div>
