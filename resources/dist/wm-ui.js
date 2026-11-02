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
