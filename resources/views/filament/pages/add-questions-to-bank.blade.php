<x-filament-panels::page>

    <div style="display:flex;flex-direction:column;gap:1.5rem">

        {{-- سربرگ رنگی --}}
        <div style="border-radius:1.25rem;padding:1.75rem;background:linear-gradient(135deg,#4f46e5,#7c3aed 55%,#ec4899);box-shadow:0 10px 30px -10px rgba(124,58,237,0.45)">
            <div style="display:flex;align-items:center;gap:1rem">
                <div style="width:3.25rem;height:3.25rem;border-radius:0.9rem;background:rgba(255,255,255,0.2);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;font-size:1.6rem">
                    ⚡
                </div>
                <div>
                    <h2 style="color:#fff;font-weight:800;font-size:1.3rem;margin:0">افزودن سریع سوال به بانک</h2>
                    <p style="color:rgba(255,255,255,0.85);font-size:0.85rem;margin:0.25rem 0 0 0">
                        مسیر آموزشی رو یک‌بار انتخاب کن، بعد هرچقدر سوال خواستی پشت‌سرهم بنویس — فرم سوال بعد از هر ذخیره خودش خالی می‌شه.
                    </p>
                </div>
            </div>

            @if ($savedCount > 0)
                <div style="margin-top:1rem;background:rgba(255,255,255,0.15);display:inline-block;padding:0.4rem 1rem;border-radius:999px;color:#fff;font-size:0.85rem;font-weight:600">
                    ✅ تا الان {{ $savedCount }} سوال ذخیره شده
                </div>
            @endif
        </div>

        {{-- مسیر آموزشی --}}
        <div style="background:var(--surface-1,#fff);border:1px solid var(--border,#e5e7eb);border-radius:1.25rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
            <h3 style="font-weight:700;font-size:1rem;margin:0 0 1rem 0;color:rgb(99,102,241)">📍 مسیر آموزشی (فقط یک‌بار پر می‌شه)</h3>
            {{ $this->contextForm }}
        </div>

        {{-- متن سوال --}}
        <div style="background:var(--surface-1,#fff);border:1px solid var(--border,#e5e7eb);border-radius:1.25rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
            <h3 style="font-weight:700;font-size:1rem;margin:0 0 1rem 0;color:rgb(236,72,153)">✏️ متن سوال (بعد از هر ذخیره خالی می‌شه)</h3>
            {{ $this->questionForm }}

            <div style="margin-top:1.75rem;border-top:1px solid var(--border,#e5e7eb);padding-top:1.25rem;display:flex;gap:0.75rem;flex-wrap:wrap">

                <x-filament::button
                    wire:click="saveAndExit"
                    color="success"
                    size="lg">
                    ✅ ذخیره و خروج
                </x-filament::button>

                <x-filament::button
                    wire:click="saveAndContinue"
                    color="primary"
                    size="lg">
                    💾 ذخیره و ایجاد سوال بعدی
                </x-filament::button>

                <a href="{{ \App\Filament\Resources\QuestionResource::getUrl('index') }}">
                    <x-filament::button
                        color="gray"
                        size="lg"
                        tag="span">
                        لغو
                    </x-filament::button>
                </a>

            </div>
        </div>

    </div>

</x-filament-panels::page>
