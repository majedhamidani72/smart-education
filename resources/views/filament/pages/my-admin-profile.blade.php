<x-filament-panels::page>

    @php
        $teacherUrl = $this->getTeacherEditUrl();
    @endphp

    <div style="display:flex;flex-direction:column;gap:1.5rem;max-width:42rem">

        {{-- سربرگ رنگی --}}
        <div style="border-radius:1.25rem;padding:2rem;background:linear-gradient(135deg,#0ea5e9,#6366f1 55%,#7c3aed);box-shadow:0 10px 30px -10px rgba(99,102,241,0.45)">
            <div style="display:flex;align-items:center;gap:1rem">
                <div style="width:3.5rem;height:3.5rem;border-radius:1rem;background:rgba(255,255,255,0.2);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;font-size:1.75rem">
                    🛡️
                </div>
                <div>
                    <h2 style="color:#fff;font-weight:800;font-size:1.4rem;margin:0">پروفایل من</h2>
                    <p style="color:rgba(255,255,255,0.85);font-size:0.9rem;margin:0.25rem 0 0 0">
                        عکس پروفایل و شماره کارت تسویه‌حسابت را اینجا مدیریت کن.
                    </p>
                </div>
            </div>
        </div>

        @if ($teacherUrl)
            <div style="background:rgba(20,184,166,0.08);border:1px solid rgba(20,184,166,0.3);border-radius:1rem;padding:1.1rem 1.3rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem">
                <div style="font-size:0.9rem;display:flex;align-items:center;gap:0.5rem">
                    <span style="font-size:1.2rem">🎓</span>
                    <span>چون نقش معلم هم داری، می‌تونی بدون خروج از سیستم، کتاب‌ها و درصد سهم معلمی‌ات رو مدیریت کنی.</span>
                </div>
                <a href="{{ $teacherUrl }}" style="background:rgb(20,184,166);color:#fff;font-weight:600;font-size:0.85rem;padding:0.55rem 1.2rem;border-radius:0.7rem;text-decoration:none;white-space:nowrap;box-shadow:0 4px 12px -2px rgba(20,184,166,0.5)">
                    برو به بخش معلمی من ←
                </a>
            </div>
        @endif

        {{-- کارت فرم --}}
        <div style="background:var(--surface-1,#fff);border:1px solid var(--border,#e5e7eb);border-radius:1.25rem;padding:1.75rem;box-shadow:0 1px 3px rgba(0,0,0,0.06)">

            <form wire:submit="save">

                {{ $this->form }}

                <div style="margin-top:1.75rem;border-top:1px solid var(--border,#e5e7eb);padding-top:1.25rem">
                    <x-filament::button type="submit" size="lg">
                        💾 ذخیره‌ی پروفایل
                    </x-filament::button>
                </div>

            </form>

        </div>

    </div>

</x-filament-panels::page>
