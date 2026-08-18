@php
    $statePath = $getStatePath();
@endphp

<div>
    @if (! $isLabelHidden() && $getLabel())
        <label for="{{ $getId() }}" style="display:block;font-size:0.875rem;font-weight:500;margin-bottom:0.375rem;color:var(--text-color,inherit)">
            {{ $getLabel() }}
            @if ($isRequired())
                <span style="color:#e11d48">*</span>
            @endif
        </label>
    @endif

    <div
        wire:ignore
        x-data="jalaliDateTimePicker({
            state: @entangle($statePath),
        })"
        x-init="init()"
        style="position:relative"
        dir="rtl"
    >
        <input
            type="text"
            id="{{ $getId() }}"
            x-model="displayValue"
            readonly
            @click="open = !open"
            placeholder="انتخاب تاریخ و ساعت"
            style="width:100%;padding:0.5rem 0.75rem;border:1px solid #d1d5db;border-radius:0.5rem;cursor:pointer;background:var(--input-bg,#fff);color:inherit;font-size:0.875rem"
            class="dark:bg-gray-800 dark:border-gray-600"
        >

        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            style="position:absolute;z-index:60;top:100%;margin-top:0.25rem;width:280px;border:1px solid #d1d5db;border-radius:0.75rem;padding:0.75rem;box-shadow:0 8px 24px rgba(0,0,0,0.12);background:#fff"
            class="dark:bg-gray-800 dark:border-gray-600"
        >
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem">
                <button type="button" @click="prevMonth()" style="padding:0.25rem 0.5rem;border-radius:0.375rem" class="hover:bg-gray-100 dark:hover:bg-gray-700">›</button>
                <span x-text="monthNames[viewMonth - 1] + ' ' + viewYear" style="font-size:0.875rem;font-weight:500"></span>
                <button type="button" @click="nextMonth()" style="padding:0.25rem 0.5rem;border-radius:0.375rem" class="hover:bg-gray-100 dark:hover:bg-gray-700">‹</button>
            </div>

            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:0.25rem">
                <template x-for="d in weekDays" :key="d">
                    <div style="text-align:center;font-size:0.7rem;color:#9ca3af;padding:0.25rem 0" x-text="d"></div>
                </template>
            </div>

            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px">
                <template x-for="cell in calendarCells" :key="cell.key">
                    <button
                        type="button"
                        x-show="cell.jd !== null"
                        @click="cell.jd !== null && selectDay(cell)"
                        x-text="cell.jd"
                        :style="cell.selected
                            ? 'background:#2563eb;color:#fff;border-radius:0.375rem;padding:0.35rem 0;font-size:0.8rem'
                            : (cell.today ? 'border:1px solid #2563eb;color:#2563eb;border-radius:0.375rem;padding:0.35rem 0;font-size:0.8rem' : 'padding:0.35rem 0;font-size:0.8rem;border-radius:0.375rem')"
                        class="hover:bg-gray-100 dark:hover:bg-gray-700"
                    ></button>
                </template>
            </div>

            <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.6rem;border-top:1px solid #e5e7eb;padding-top:0.5rem" class="dark:border-gray-600">
                <span style="font-size:0.8rem">ساعت</span>
                <select x-model.number="hh" style="border:1px solid #d1d5db;border-radius:0.375rem;padding:0.25rem;font-size:0.8rem" class="dark:bg-gray-800 dark:border-gray-600">
                    <template x-for="h in 24" :key="h"><option :value="h-1" x-text="String(h-1).padStart(2,'0')"></option></template>
                </select>
                <span>:</span>
                <select x-model.number="mi" style="border:1px solid #d1d5db;border-radius:0.375rem;padding:0.25rem;font-size:0.8rem" class="dark:bg-gray-800 dark:border-gray-600">
                    <template x-for="m in 60" :key="m"><option :value="m-1" x-text="String(m-1).padStart(2,'0')"></option></template>
                </select>

                <button type="button" @click="applyTimeAndClose()" style="margin-right:auto;background:#2563eb;color:#fff;font-size:0.8rem;padding:0.35rem 0.75rem;border-radius:0.375rem">تایید</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {

                /*
                |--------------------------------------------------------------------------
                | تبدیل شمسی <-> میلادی (الگوریتم استاندارد بر پایه‌ی
                | شماره‌ی روز ژولینی — همان الگوریتمی که کتابخانه‌های
                | معروف جاوااسکریپت مثل jalaali-js استفاده می‌کنند)
                |--------------------------------------------------------------------------
                */
                const div = (a, b) => Math.floor(a / b);
                const mod = (a, b) => a - div(a, b) * b;

                function jalCal(jy) {
                    const breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
                    const bl = breaks.length;
                    const gy = jy + 621;
                    let leapJ = -14, jp = breaks[0], jm, jump = 0, n, i;

                    for (i = 1; i < bl; i += 1) {
                        jm = breaks[i];
                        jump = jm - jp;
                        if (jy < jm) break;
                        leapJ = leapJ + div(jump, 33) * 8 + div(mod(jump, 33), 4);
                        jp = jm;
                    }
                    n = jy - jp;
                    leapJ = leapJ + div(n, 33) * 8 + div(mod(n, 33) + 3, 4);
                    if (mod(jump, 33) === 4 && jump - n === 4) leapJ += 1;
                    const leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;
                    const march = 20 + leapJ - leapG;
                    if (jump - n < 6) n = n - jump + div(jump, 33) * 33;
                    let leap = mod(mod(n + 1, 33) - 1, 4);
                    if (leap === -1) leap = 4;
                    return { leap, gy, march };
                }

                function g2d(gy, gm, gd) {
                    let d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4)
                        + div(153 * mod(gm + 9, 12) + 2, 5)
                        + gd - 34840408;
                    d = d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
                    return d;
                }

                function d2g(jdn) {
                    let j = 4 * jdn + 139361631;
                    j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
                    const i = div(mod(j, 1461), 4) * 5 + 308;
                    const gd = div(mod(i, 153), 5) + 1;
                    const gm = mod(div(i, 153), 12) + 1;
                    const gy = div(j, 1461) - 100100 + div(8 - gm, 6);
                    return { gy, gm, gd };
                }

                function j2d(jy, jm, jd) {
                    const r = jalCal(jy);
                    return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1;
                }

                function d2j(jdn) {
                    const gy = d2g(jdn).gy;
                    let jy = gy - 621;
                    const r = jalCal(jy);
                    const jdn1f = g2d(gy, 3, r.march);
                    let k = jdn - jdn1f, jm, jd;

                    if (k >= 0) {
                        if (k <= 185) {
                            jm = 1 + div(k, 31);
                            jd = mod(k, 31) + 1;
                            return { jy, jm, jd };
                        }
                        k -= 186;
                    } else {
                        jy -= 1;
                        k += 179;
                        if (r.leap === 1) k += 1;
                    }
                    jm = 7 + div(k, 30);
                    jd = mod(k, 30) + 1;
                    return { jy, jm, jd };
                }

                function gregorianToJalali(gy, gm, gd) {
                    const r = d2j(g2d(gy, gm, gd));
                    return [r.jy, r.jm, r.jd];
                }

                function jalaliToGregorian(jy, jm, jd) {
                    const r = d2g(j2d(jy, jm, jd));
                    return [r.gy, r.gm, r.gd];
                }

                function jalaliDaysInMonth(jy, jm) {
                    if (jm <= 6) return 31;
                    if (jm <= 11) return 30;
                    // اسفند: بسته به کبیسه بودن سال
                    const r = jalCal(jy);
                    return r.leap === 1 ? 30 : 29;
                }

                window.Alpine.data('jalaliDateTimePicker', ({ state }) => {

                    // مقدار اولیه را همین‌جا (نه توی init) و به‌صورت
                    // فوری محاسبه می‌کنیم تا هیچ‌وقت viewYear/viewMonth
                    // خالی (null) نباشند. قبلاً چون این مقادیر تا قبل
                    // از اجرای init() برابر null بودند، زدن دکمه‌ی
                    // «ماه قبل» باعث می‌شد null-1 به یک عدد نامعتبر
                    // (۱-) تبدیل شود و دیگر هیچ‌وقت خودش را درست
                    // نمی‌کرد — همان چیزی که باعث می‌شد اسم ماه، جای
                    // فارسی، «undefined» (به نظر انگلیسی) نشان بدهد.
                    const now = new Date();
                    const initial = gregorianToJalali(
                        now.getFullYear(),
                        now.getMonth() + 1,
                        now.getDate()
                    );

                    return {
                    open: false,
                    state,
                    displayValue: '',
                    jy: initial[0], jm: initial[1], jd: initial[2],
                    hh: now.getHours(), mi: now.getMinutes(),
                    viewYear: initial[0], viewMonth: initial[1],
                    monthNames: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'],
                    weekDays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
                    calendarCells: [],

                    init() {
                        this.$watch('state', () => this.syncFromState());
                        this.syncFromState();
                    },

                    syncFromState() {
                        let date;

                        if (this.state) {
                            date = new Date(this.state.replace(' ', 'T'));
                        } else {
                            date = new Date();
                        }

                        const [jy, jm, jd] = gregorianToJalali(
                            date.getFullYear(),
                            date.getMonth() + 1,
                            date.getDate()
                        );

                        this.jy = jy;
                        this.jm = jm;
                        this.jd = jd;
                        this.hh = date.getHours();
                        this.mi = date.getMinutes();
                        this.viewYear = jy;
                        this.viewMonth = jm;

                        this.updateDisplay();
                        this.buildCalendar();

                        // اگر هنوز مقداری ثبت نشده (فیلد خالی بود)،
                        // همین لحظه‌ی فعلی را به‌عنوان پیش‌فرض ذخیره کن.
                        if (! this.state) {
                            this.writeState();
                        }
                    },

                    updateDisplay() {
                        const pad = (n) => String(n).padStart(2, '0');
                        this.displayValue = `${this.jy}/${pad(this.jm)}/${pad(this.jd)} ${pad(this.hh)}:${pad(this.mi)}`;
                    },

                    buildCalendar() {
                        const totalDays = jalaliDaysInMonth(this.viewYear, this.viewMonth);

                        // پیدا کردن روز هفته‌ی اول ماه (۰=شنبه ... ۶=جمعه)
                        const [gy, gm, gd] = jalaliToGregorian(this.viewYear, this.viewMonth, 1);
                        const jsDay = new Date(gy, gm - 1, gd).getDay(); // 0=یکشنبه در جاوااسکریپت
                        const startOffset = (jsDay + 1) % 7; // تبدیل به شنبه=۰

                        const today = new Date();
                        const [tjy, tjm, tjd] = gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());

                        const cells = [];

                        for (let i = 0; i < startOffset; i++) {
                            cells.push({ key: 'empty-' + i, jd: null });
                        }

                        for (let day = 1; day <= totalDays; day++) {
                            cells.push({
                                key: 'day-' + day,
                                jd: day,
                                selected: (this.viewYear === this.jy && this.viewMonth === this.jm && day === this.jd),
                                today: (this.viewYear === tjy && this.viewMonth === tjm && day === tjd),
                            });
                        }

                        this.calendarCells = cells;
                    },

                    prevMonth() {
                        this.viewMonth -= 1;
                        if (this.viewMonth < 1) { this.viewMonth = 12; this.viewYear -= 1; }
                        this.buildCalendar();
                    },

                    nextMonth() {
                        this.viewMonth += 1;
                        if (this.viewMonth > 12) { this.viewMonth = 1; this.viewYear += 1; }
                        this.buildCalendar();
                    },

                    selectDay(cell) {
                        this.jy = this.viewYear;
                        this.jm = this.viewMonth;
                        this.jd = cell.jd;
                        this.buildCalendar();
                        this.updateDisplay();
                    },

                    applyTimeAndClose() {
                        this.updateDisplay();
                        this.writeState();
                        this.open = false;
                    },

                    writeState() {
                        const [gy, gm, gd] = jalaliToGregorian(this.jy, this.jm, this.jd);
                        const pad = (n) => String(n).padStart(2, '0');
                        this.state = `${gy}-${pad(gm)}-${pad(gd)} ${pad(this.hh)}:${pad(this.mi)}:00`;
                    },
                    };
                });
            });
        </script>
    @endpush
@endonce
