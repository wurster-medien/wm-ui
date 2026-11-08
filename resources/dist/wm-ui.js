/* Datumsauswahl (WmDatePicker) und Zeitraum (wmDateRangePicker), siehe UiServiceProvider. */
window.WmDatePicker ??= (() => {
    const ICONS = {
        previous: 'M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z',
        next: 'M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z',
    }

    function makeButton(className, label, content) {
        const button = document.createElement('button')
        button.type = 'button'
        button.className = className
        button.title = label
        button.setAttribute('aria-label', label)
        button.innerHTML = content

        return button
    }

    function navButton(direction, label) {
        const button = makeButton(
            'wm-dp-nav',
            label,
            `<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="${ICONS[direction]}"/></svg>`,
        )
        button.dataset.hubDpNav = direction

        return button
    }

    return {
        /**
         * @param {HTMLElement} root Element mit x-data="dateTimePickerFormComponent(…)"
         * @param {object} picker Daten der Filament-Komponente ($data)
         * @param {Function} watch $watch der Komponente (endet mit dem Element)
         * @param {object} options clearable: „Leeren“ anbieten (Feld ohne Pflicht)
         */
        enhance(root, picker, watch, options = {}) {
            const panel = root.querySelector('.fi-fo-date-time-picker-panel')
            const header = panel?.querySelector('.fi-fo-date-time-picker-panel-header')

            if (! header || root.dataset.wmDatePicker) {
                return
            }

            root.dataset.wmDatePicker = 'enhanced'

            const dayjs = window.dayjs
            const now = () => (dayjs.tz ? dayjs().tz(dayjs.tz.guess()) : dayjs())
            const focused = () => picker.focusedDate ?? now()
            const shift = (amount, unit) => {
                picker.focusedDate = focused().add(amount, unit)
            }

            const previous = navButton('previous', 'Vorheriger Monat')
            const next = navButton('next', 'Nächster Monat')
            previous.addEventListener('click', (event) => shift(-1, event.shiftKey ? 'year' : 'month'))
            next.addEventListener('click', (event) => shift(1, event.shiftKey ? 'year' : 'month'))
            header.prepend(previous)
            const year = document.createElement('select')
            year.className = 'fi-fo-date-time-picker-month-select wm-dp-year'
            year.setAttribute('aria-label', 'Jahr')
            year.addEventListener('change', () => {
                const date = focused()
                const target = date.date(1).year(Number(year.value))
                picker.focusedDate = target.date(Math.min(date.date(), target.daysInMonth()))
            })
            header.querySelector('.fi-fo-date-time-picker-year-input').after(year)
            header.append(next)

            let yearRange = ''
            const updateYears = (shown, min, max) => {
                const from = Math.min(shown, Math.max(shown - 10, min?.year() ?? -Infinity))
                const until = Math.max(shown, Math.min(shown + 10, max?.year() ?? Infinity))

                if (yearRange !== `${from}-${until}`) {
                    yearRange = `${from}-${until}`
                    year.replaceChildren(...Array.from({ length: until - from + 1 }, (_, index) => new Option(from + index, from + index)))
                }

                year.value = shown
            }

            const footer = document.createElement('div')
            footer.className = 'wm-dp-footer'

            const today = makeButton('wm-dp-action', 'Heute', 'Heute')
            today.dataset.hubDpAction = 'today'
            today.addEventListener('click', () => {
                const date = now()
                picker.focusedDate = date

                if (! picker.dateIsDisabled(date)) {
                    picker.selectDate()
                }
            })
            footer.append(today)

            const clear = makeButton('wm-dp-action', 'Auswahl entfernen', 'Leeren')
            clear.dataset.hubDpAction = 'clear'
            clear.hidden = true
            clear.addEventListener('click', () => {
                picker.clearState()

                if (picker.isOpen()) {
                    picker.togglePanelVisibility()
                }
            })

            if (options.clearable) {
                footer.append(clear)
            }

            panel.append(footer)

            root.addEventListener('keydown', (event) => {
                if (! ['PageUp', 'PageDown'].includes(event.key) || ! picker.isOpen()) {
                    return
                }

                event.preventDefault()
                shift(event.key === 'PageUp' ? -1 : 1, event.shiftKey ? 'year' : 'month')
            })

            const update = () => {
                const month = focused()
                const min = picker.getMinDate()
                const max = picker.getMaxDate()

                previous.disabled = !! min && month.subtract(1, 'month').endOf('month').isBefore(min, 'day')
                next.disabled = !! max && month.add(1, 'month').startOf('month').isAfter(max, 'day')
                updateYears(month.year(), min, max)

                const date = now()
                const selectable = ! picker.dateIsDisabled(date)
                today.title = `Heute, ${date.format('DD.MM.YYYY')}` + (selectable ? '' : ' (hier nicht wählbar)')

                clear.hidden = ! picker.state
            }

            watch('focusedDate', update)
            watch('state', update)
            setTimeout(update)
        },
    }
})()

/**
 * Zeitraum in einem Feld (x-date-range-picker). Werte als Y-m-d-Text, gerechnet mit Date in der Zeit des Browsers.
 * Klick 1 = Beginn, Klick 2 = Ende (früher als der Beginn: neuer Beginn). Übernommen wird mit dem Ende oder beim
 * Schließen (dann nur der Beginn), vorher bleibt alles im Entwurf.
 */
;(() => {
    const MONTHS = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember']
    const pad = (number) => String(number).padStart(2, '0')
    const isoOf = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
    const normalize = (value) => (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value) ? value.slice(0, 10) : null)
    const utcOf = (iso) => Date.UTC(Number(iso.slice(0, 4)), Number(iso.slice(5, 7)) - 1, Number(iso.slice(8, 10)))
    const formatDay = (iso, withYear = true) => `${iso.slice(8, 10)}.${iso.slice(5, 7)}.` + (withYear ? iso.slice(0, 4) : '')

    const register = () => window.Alpine.data('wmDateRangePicker', ({ start, end, min = null, max = null, endOptional = false, placeholder = 'Zeitraum wählen' }) => ({
        start,
        end,
        min: normalize(min),
        max: normalize(max),
        endOptional,
        placeholder,
        draftStart: null,
        draftEnd: null,
        picking: false,
        hovered: null,
        today: isoOf(new Date()),
        viewYear: new Date().getFullYear(),
        viewMonth: new Date().getMonth(),
        months: MONTHS,

        init() {
            this.reset()
            this.$watch('start', () => this.picking || this.reset())
            this.$watch('end', () => this.picking || this.reset())

            this.$nextTick(() => {
                const panel = this.$refs.panel
                const close = panel.close

                panel.close = (...args) => {
                    const result = close.apply(panel, args)
                    this.finish()

                    return result
                }
            })
        },

        reset() {
            this.draftStart = normalize(this.start)
            this.draftEnd = normalize(this.end)
            this.picking = false
            this.hovered = null
        },

        isOpen() {
            return !! this.$refs.panel?._x_isShown
        },

        toggle() {
            if (! this.isOpen()) {
                this.reset()
                this.show(this.draftStart ?? this.clamp(this.today))
            }

            this.$refs.panel.toggle(this.$refs.button)
        },

        show(iso) {
            this.viewYear = Number(iso.slice(0, 4))
            this.viewMonth = Number(iso.slice(5, 7)) - 1
        },

        clamp(iso) {
            if (this.min && iso < this.min) {
                return this.min
            }

            return this.max && iso > this.max ? this.max : iso
        },

        shiftMonth(months) {
            const date = new Date(this.viewYear, this.viewMonth + months, 1)
            this.viewYear = date.getFullYear()
            this.viewMonth = date.getMonth()
        },

        canShift(direction) {
            if (direction < 0) {
                return ! this.min || isoOf(new Date(this.viewYear, this.viewMonth, 0)) >= this.min
            }

            return ! this.max || isoOf(new Date(this.viewYear, this.viewMonth + 1, 1)) <= this.max
        },

        get years() {
            const from = Math.min(this.viewYear, Math.max(this.viewYear - 10, this.min ? Number(this.min.slice(0, 4)) : -Infinity))
            const until = Math.max(this.viewYear, Math.min(this.viewYear + 10, this.max ? Number(this.max.slice(0, 4)) : Infinity))

            return Array.from({ length: until - from + 1 }, (_, index) => from + index)
        },

        get cells() {
            const first = new Date(this.viewYear, this.viewMonth, 1)
            const offset = (first.getDay() + 6) % 7
            const days = new Date(this.viewYear, this.viewMonth + 1, 0).getDate()
            const count = Math.ceil((offset + days) / 7) * 7

            return Array.from({ length: count }, (_, index) => {
                const date = new Date(this.viewYear, this.viewMonth, 1 - offset + index)

                return { iso: isoOf(date), day: date.getDate(), outside: date.getMonth() !== this.viewMonth }
            })
        },

        isDisabled(iso) {
            return !! ((this.min && iso < this.min) || (this.max && iso > this.max))
        },

        /** Ende des Bereichs zum Hervorheben: beim Wählen der Tag unter der Maus. */
        get rangeEnd() {
            if (! this.picking) {
                return this.draftEnd
            }

            return this.hovered && this.hovered >= this.draftStart ? this.hovered : null
        },

        dayClasses(cell) {
            const from = this.draftStart
            const until = this.rangeEnd
            const isRange = !! (from && until && until !== from)

            return {
                'fi-fo-date-time-picker-calendar-day-today': cell.iso === this.today,
                'fi-selected': cell.iso === from || cell.iso === until,
                'fi-focused': cell.iso === this.hovered,
                'fi-disabled': this.isDisabled(cell.iso),
                'wm-dp-outside': cell.outside,
                'wm-drp-in-range': isRange && cell.iso > from && cell.iso < until,
                'wm-drp-range-start': isRange && cell.iso === from,
                'wm-drp-range-end': isRange && cell.iso === until,
            }
        },

        pick(cell) {
            if (this.isDisabled(cell.iso)) {
                return
            }

            if (cell.outside) {
                this.show(cell.iso)
            }

            if (! this.picking || ! this.draftStart || cell.iso < this.draftStart) {
                this.draftStart = cell.iso
                this.draftEnd = null
                this.picking = true

                return
            }

            this.draftEnd = cell.iso
            this.picking = false
            this.commit()
            this.$refs.panel.close()
        },

        pickToday() {
            this.show(this.today)
            this.pick({ iso: this.today, outside: false })
        },

        clear() {
            this.draftStart = null
            this.draftEnd = null
            this.picking = false
            this.commit()
            this.$refs.panel.close()
        },

        /** Beim Schließen mitten in der Auswahl gilt der Beginn allein. */
        finish() {
            if (this.picking) {
                this.picking = false
                this.commit()
            }

            this.hovered = null
        },

        commit() {
            this.start = this.draftStart
            this.end = this.draftEnd
        },

        get hasValue() {
            return !! (this.draftStart || this.draftEnd)
        },

        get text() {
            const from = this.draftStart
            const until = this.draftEnd

            if (from && until) {
                return `${formatDay(from, from.slice(0, 4) !== until.slice(0, 4))} – ${formatDay(until)}`
            }

            if (from) {
                return this.endOptional ? `ab ${formatDay(from)}` : `${formatDay(from)} – …`
            }

            return until ? `bis ${formatDay(until)}` : this.placeholder
        },

        get daysText() {
            if (! this.draftStart || ! this.draftEnd) {
                return ''
            }

            const days = Math.round((utcOf(this.draftEnd) - utcOf(this.draftStart)) / 86400000) + 1

            return days === 1 ? '1 Tag' : `${days} Tage`
        },

        get hint() {
            if (! this.picking) {
                return ''
            }

            return this.endOptional ? 'Ende wählen (optional)' : 'Ende wählen'
        },

        get todayTitle() {
            return `Heute, ${formatDay(this.today)}` + (this.isDisabled(this.today) ? ' (hier nicht wählbar)' : '')
        },
    }))

    if (window.Alpine) {
        register()
    } else {
        document.addEventListener('alpine:init', register)
    }
})()

/*
 * Abfragen im Hintergrund (wire:poll): Am Handy scheitert leicht eine, wenn es aufwacht oder die App wechselt, die nächste
 * klappt wieder. Dafür keine Meldung „Fehler beim Laden der Seite“ (Simon, 3.11.2026), die gilt nur, wenn jemand etwas
 * getan hat. Eine abgelaufene Sitzung lädt die Seite neu, statt die Fehlerseite über die Liste zu legen; der Status ist dabei
 * nicht immer 419, weil Laravel Anfragen der eigenen Seite auch ohne Token durchlässt. Fehler des Servers (5xx) bleiben sichtbar.
 *
 * Filament fragt window.filamentErrorNotifications beim Scheitern ab und kennt keine Ausnahme für Abfragen. Darum läuft
 * dieser Teil über capture vor Filament, nimmt den Wert für diesen Durchlauf weg und setzt ihn gleich danach wieder.
 */
document.addEventListener('livewire:init', () => {
    const RELOADED_AT = 'wm-session-reload'

    const onlyPolls = (request) => {
        const messages = [...(request?.messages ?? [])]

        return messages.length > 0 && messages.every((message) => [...message.actions].every((action) => action.metadata?.type === 'poll'))
    }

    const skipFilamentNotice = () => {
        const notifications = window.filamentErrorNotifications
        window.filamentErrorNotifications = null
        queueMicrotask(() => (window.filamentErrorNotifications = notifications))
    }

    /** Höchstens einmal in zehn Minuten, falls auch die neu geladene Seite so antwortet. */
    const reloadOnce = () => {
        try {
            if (Date.now() - Number(sessionStorage.getItem(RELOADED_AT) ?? 0) < 600000) {
                return
            }

            sessionStorage.setItem(RELOADED_AT, String(Date.now()))
        } catch (error) {
            return
        }

        window.location.reload()
    }

    Livewire.interceptRequest(({ request, onFailure, onError }) => {
        onFailure(() => {
            if (onlyPolls(request)) {
                skipFilamentNotice()
            }
        })

        onError(({ response, preventDefault }) => {
            if (response?.status >= 400 && response.status < 500 && onlyPolls(request)) {
                preventDefault()
                skipFilamentNotice()
                reloadOnce()
            }
        })
    })
}, { capture: true })

/*
 * Kopfzeilen der Tabellen bleiben beim Scrollen stehen (Simon, 3.11.2026: „alle horizontalen th sticky, damit sie
 * mitscrollen“; Verschieben per Skript hinkte beim Scrollen ein Bild hinterher). Echtes position: sticky (wm-ui.css),
 * das Skript stellt nur die Rahmen ein: Passt die Tabelle in die Breite, werden ihre Rahmen mit overflow hidden/auto zu
 * clip (sieht gleich aus, hält sticky aber nicht fest), die Kopfzeile klebt unter der Kopfleiste. Ist sie breiter, wird
 * ihr scrollbarer Rahmen höchstens so hoch wie das Fenster und die Kopfzeile klebt darin oben. Nicht in Fenstern.
 */
;(() => {
    const CLIP = 'wm-sticky-clip'
    const BOX = 'wm-sticky-box'
    const PAGE_HEAD = 'wm-sticky-page'
    const BOX_HEAD = 'wm-sticky-box-head'
    let frame = null

    // Eigene Tabellen haben keinen Hintergrund im Kopf, darunter liefen die Zeilen sichtbar durch (Simon, 9.11.2026,
    // Nutzung): Die Kopfzeile bekommt die Hintergründe ihrer Rahmen übereinander, bis einer deckt (weiße Karte,
    // getönter Kasten, dunkles Design).
    const alphaOf = (color) => {
        if (color === 'transparent') {
            return 0
        }

        const slash = color.match(/\/\s*([\d.]+)(%?)\s*\)$/)

        if (slash) {
            return parseFloat(slash[1]) / (slash[2] ? 100 : 1)
        }

        const parts = color.match(/^rgba\(([^)]*)\)$/)?.[1].split(',')

        return parts?.length === 4 ? parseFloat(parts[3]) : 1
    }

    const paint = (head) => {
        const layers = []

        for (let element = head; element; element = element.parentElement) {
            const color = getComputedStyle(element).backgroundColor
            const alpha = alphaOf(color)

            if (alpha > 0) {
                layers.push(color)
            }

            if (alpha >= 1) {
                break
            }
        }

        const base = layers.length && alphaOf(layers.at(-1)) >= 1 ? layers.pop() : null
        head.style.setProperty('--wm-thead-layers', layers.length ? layers.map((color) => `linear-gradient(${color}, ${color})`).join(', ') : 'none')

        if (base) {
            head.style.setProperty('--wm-thead-bg', base)
        } else {
            head.style.removeProperty('--wm-thead-bg')
        }
    }

    const overflows = (element) => {
        const style = getComputedStyle(element)

        return [style.overflowX, style.overflowY].some((value) => ['hidden', 'auto', 'scroll'].includes(value))
    }

    const update = () => {
        frame = null
        const bar = document.querySelector('.fi-topbar-ctn')
        const top = bar && ['sticky', 'fixed'].includes(getComputedStyle(bar).position) ? bar.offsetHeight : 0
        document.documentElement.style.setProperty('--wm-thead-top', `${top}px`)

        // Ohne seinen Rahmen vergisst der Browser die Scroll-Position, deshalb merken und unten zurücksetzen (Simon,
        // 6.11.2026: jede Aktualisierung der Seite, etwa die Telefon-Box alle 3 Sekunden, warf die Liste nach oben).
        const positions = new Map()

        for (const element of document.querySelectorAll(`.${BOX}`)) {
            positions.set(element, [element.scrollTop, element.scrollLeft])
        }

        for (const element of document.querySelectorAll(`.${CLIP}, .${BOX}`)) {
            element.classList.remove(CLIP, BOX)
        }

        for (const head of document.querySelectorAll('.fi-main table > thead')) {
            head.classList.remove(PAGE_HEAD, BOX_HEAD)
            head.style.removeProperty('--wm-thead-layers')
            head.style.removeProperty('--wm-thead-bg')

            // Unsichtbare Kopfzeilen (nur für Screenreader, position absolute) bleiben, wie sie sind.
            if (head.closest('.fi-modal') || head.offsetHeight === 0 || getComputedStyle(head).position === 'absolute') {
                continue
            }

            paint(head)

            const table = head.parentElement
            const frames = []

            for (let element = table.parentElement; element && element !== document.documentElement; element = element.parentElement) {
                if (overflows(element)) {
                    frames.push(element)
                }
            }

            const scroller = frames.find((element) => ['auto', 'scroll'].includes(getComputedStyle(element).overflowX))

            if (scroller && table.offsetWidth > scroller.clientWidth + 1) {
                scroller.classList.add(BOX)
                head.classList.add(BOX_HEAD)
            } else {
                frames.forEach((element) => element.classList.add(CLIP))
                head.classList.add(PAGE_HEAD)
            }
        }

        for (const [element, [top, left]] of positions) {
            if (element.classList.contains(BOX)) {
                element.scrollTop = top
                element.scrollLeft = left
            }
        }
    }

    const schedule = () => {
        frame ??= requestAnimationFrame(update)
    }

    window.addEventListener('resize', schedule)
    document.addEventListener('livewire:navigated', schedule)
    document.addEventListener('DOMContentLoaded', schedule)
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', schedule)
        Livewire.hook('morph.added', schedule)
    })
})()

/*
 * Auswahlfelder ohne die Liste des Browsers (Simon, 8.11.2026: „einheitlich, aber nicht auf die Browser-Liste, mach es
 * schön“): Jedes einfache <select class="fi-select-input"> in einem Eingabefeld von Filament (Tabellenfilter, „pro Seite“,
 * Formulare, eigene Seiten) öffnet stattdessen ein Menü im Stil von Filament, am Rechner und am Handy. Das <select> bleibt
 * der Wert (Livewire, Alpine, Formulare lesen es wie bisher), das Menü setzt ihn und meldet input und change. Ab neun
 * Einträgen sucht Tippen in der Liste, mit `data-wm-search` am <select> immer (Simon, 8.11.2026: Tags der Mediendatenbank). Der Fokus bleibt immer im Feld, damit Fenster mit Fokusfalle nichts merken; das
 * Menü hängt an <body>. Felder mit Suche (Filaments eigene Auswahl) bleiben, wie sie sind.
 */
;(() => {
    const FIELD = '.fi-input-wrp-content-ctn > select.fi-select-input:not([multiple])'
    const SEARCH_FROM = 9
    const CHECK = '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>'
    const coarse = window.matchMedia('(pointer: coarse)')
    let open = null

    document.documentElement.classList.add('wm-selects')

    const fold = (text) => text.toLocaleLowerCase('de').normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/ß/g, 'ss')

    const fieldOf = (target) => {
        const wrapper = target.closest?.('.fi-input-wrp')

        if (! wrapper || target.closest('button, a, input, textarea, [contenteditable]')) {
            return null
        }

        const fields = wrapper.querySelectorAll(FIELD)

        return fields.length === 1 && ! fields[0].disabled ? fields[0] : null
    }

    const close = (refocus = false) => {
        if (! open) {
            return
        }

        const { select, panel } = open
        open = null
        panel.remove()
        select.removeAttribute('aria-expanded')

        if (refocus && ! coarse.matches) {
            select.focus({ preventScroll: true })
        }
    }

    const place = () => {
        if (! open) {
            return
        }

        const { select, panel } = open
        const wrapper = select.closest('.fi-input-wrp')

        if (! wrapper?.isConnected) {
            close()

            return
        }

        const box = wrapper.getBoundingClientRect()
        const gap = 4
        const margin = 8
        const width = Math.min(Math.max(box.width, 192), window.innerWidth - 2 * margin)
        const below = window.innerHeight - box.bottom - gap - margin
        const above = box.top - gap - margin
        const up = below < 200 && above > below

        panel.style.width = `${width}px`
        panel.style.left = `${Math.max(margin, Math.min(box.left, window.innerWidth - width - margin))}px`
        panel.style.maxHeight = `${Math.min(320, up ? above : below)}px`
        panel.style.top = up ? '' : `${box.bottom + gap}px`
        panel.style.bottom = up ? `${window.innerHeight - box.top + gap}px` : ''
    }

    const highlight = (index) => {
        open.active = index

        open.items.forEach(({ item }, position) => item.classList.toggle('wm-active', position === index))
        open.items[index]?.item.scrollIntoView({ block: 'nearest' })
    }

    const render = () => {
        const { select, list, search, query } = open
        const words = fold(query).split(/\s+/).filter(Boolean)
        let group = null
        list.replaceChildren()
        open.items = []

        for (const option of select.options) {
            if (option.hidden) {
                continue
            }

            const label = option.textContent.trim()

            if (words.length > 0 && ! words.every((word) => fold(label).includes(word))) {
                continue
            }

            const optgroup = option.parentElement.tagName === 'OPTGROUP' ? option.parentElement.label : null

            if (optgroup !== group && optgroup) {
                const heading = document.createElement('div')
                heading.className = 'wm-select-group'
                heading.textContent = optgroup
                list.append(heading)
            }

            group = optgroup
            const item = document.createElement('div')
            item.className = 'wm-select-option'
            item.setAttribute('role', 'option')
            item.setAttribute('aria-selected', String(option.selected))
            item.classList.toggle('wm-selected', option.selected)
            item.classList.toggle('wm-disabled', option.disabled)
            item.innerHTML = `<span></span>${option.selected ? CHECK : ''}`
            item.firstChild.textContent = label || ' '
            item.dataset.index = String(open.items.length)
            list.append(item)
            open.items.push({ option, item })
        }

        if (open.items.length === 0) {
            const empty = document.createElement('div')
            empty.className = 'wm-select-empty'
            empty.textContent = 'Keine Treffer'
            list.append(empty)
        }

        if (search) {
            search.textContent = query
            search.classList.toggle('wm-select-search-empty', query === '')
        }

        const selected = open.items.findIndex(({ option }) => option.selected)
        highlight(words.length > 0 ? open.items.findIndex(({ option }) => ! option.disabled) : selected)
    }

    const move = (by) => {
        const count = open.items.length

        for (let step = 1; step <= count; step++) {
            const index = ((open.active < 0 ? (by > 0 ? -1 : 0) : open.active) + by * step + count) % count

            if (! open.items[index].option.disabled) {
                highlight(index)

                return
            }
        }
    }

    const choose = (index) => {
        const entry = open?.items[index]

        if (! entry || entry.option.disabled) {
            return
        }

        const { select } = open
        const changed = select.value !== entry.option.value
        close(true)

        if (changed) {
            select.value = entry.option.value
            select.dispatchEvent(new Event('input', { bubbles: true }))
            select.dispatchEvent(new Event('change', { bubbles: true }))
        }
    }

    const show = (select) => {
        if (open?.select === select) {
            close(true)

            return
        }

        close()

        const panel = document.createElement('div')
        panel.className = 'wm-select-panel'
        panel.innerHTML = '<div class="wm-select-list" role="listbox"></div>'
        const list = panel.firstChild
        const label = select.getAttribute('aria-label') ?? select.labels?.[0]?.textContent.trim()

        if (label) {
            list.setAttribute('aria-label', label)
        }

        let search = null

        if ((select.options.length >= SEARCH_FROM || select.hasAttribute('data-wm-search')) && ! coarse.matches) {
            search = document.createElement('div')
            search.className = 'wm-select-search wm-select-search-empty'
            search.dataset.placeholder = 'Tippen zum Suchen'
            panel.prepend(search)
        }

        // Ein Klick ins Menü nimmt dem Feld nie den Fokus.
        panel.addEventListener('mousedown', (event) => event.preventDefault())
        panel.addEventListener('click', (event) => {
            const item = event.target.closest('.wm-select-option')

            if (item) {
                choose(Number(item.dataset.index))
            }
        })
        panel.addEventListener('mousemove', (event) => {
            const item = event.target.closest('.wm-select-option')

            if (item && open && Number(item.dataset.index) !== open.active && ! item.classList.contains('wm-disabled')) {
                highlight(Number(item.dataset.index))
            }
        })

        document.body.append(panel)
        open = { select, panel, list, search, query: '', items: [], active: -1 }
        select.setAttribute('aria-expanded', 'true')

        if (! coarse.matches) {
            select.focus({ preventScroll: true })
        }

        place()
        render()
    }

    document.addEventListener('click', (event) => {
        if (open && ! open.panel.contains(event.target) && ! open.select.closest('.fi-input-wrp')?.contains(event.target)) {
            close()
        }

        const select = fieldOf(event.target)

        if (select) {
            // Liegt das Feld in einem <label>, schickt der Browser danach einen zweiten Klick ans <select>, der das Menü
            // gleich wieder schließen würde (Simon, 9.11.2026: Sicherungen).
            if (event.target !== select && event.target.closest('label')?.control === select) {
                event.preventDefault()
            }

            show(select)
        }
    })

    // Ein Klick auf die Beschriftung des Feldes (label for) fokussiert das <select>; am Handy öffnete das die Liste.
    document.addEventListener('focusin', (event) => {
        if (coarse.matches && event.target.matches?.(FIELD)) {
            event.target.blur()
        }
    })

    document.addEventListener('keydown', (event) => {
        if (! open) {
            if (event.target.matches?.(FIELD) && (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key) || (event.altKey && event.key === 'ArrowDown'))) {
                event.preventDefault()
                show(event.target)
            }

            return
        }

        const key = event.key

        if (key === 'Escape') {
            event.preventDefault()
            event.stopPropagation()
            close(true)
        } else if (key === 'Tab') {
            close()
        } else if (key === 'ArrowDown' || key === 'ArrowUp') {
            event.preventDefault()
            move(key === 'ArrowDown' ? 1 : -1)
        } else if (key === 'Home' || key === 'End') {
            event.preventDefault()
            highlight(key === 'Home' ? 0 : open.items.length - 1)
        } else if (key === 'Enter' || (key === ' ' && ! open.search)) {
            event.preventDefault()
            choose(open.active)
        } else if (open.search && key === 'Backspace') {
            event.preventDefault()
            open.query = open.query.slice(0, -1)
            render()
        } else if (key.length === 1 && ! event.ctrlKey && ! event.metaKey && ! event.altKey) {
            event.preventDefault()

            if (open.search) {
                open.query += key
                render()
            } else {
                // Ohne Suche springt ein Buchstabe zum nächsten Eintrag, der so anfängt.
                const start = open.active + 1
                const found = [...open.items.slice(start), ...open.items.slice(0, start)]
                    .find(({ option }) => ! option.disabled && fold(option.textContent.trim()).startsWith(fold(key)))

                if (found) {
                    highlight(open.items.indexOf(found))
                }
            }
        }
    }, true)

    window.addEventListener('resize', () => close())
    document.addEventListener('scroll', (event) => {
        if (open && ! open.panel.contains(event.target)) {
            place()
        }
    }, true)
    document.addEventListener('livewire:navigating', () => close())
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', () => requestAnimationFrame(place))
    })
})()
