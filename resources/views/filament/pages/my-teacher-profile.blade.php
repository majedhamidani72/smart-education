<x-filament-panels::page>

    @php
        $adminUrl = $this->getAdminProfileUrl();
    @endphp

    @if ($adminUrl)
        <div style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.25);border-radius:0.85rem;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem">
            <div style="font-size:0.9rem">
                🛡️ چون نقش ادمین هم داری، می‌تونی بدون خروج از سیستم برگردی به پروفایل ادمینی‌ات.
            </div>
            <a href="{{ $adminUrl }}" style="background:rgb(99,102,241);color:#fff;font-weight:600;font-size:0.85rem;padding:0.5rem 1.1rem;border-radius:0.6rem;text-decoration:none;white-space:nowrap">
                برو به بخش ادمینی من ←
            </a>
        </div>
    @endif

    <form wire:submit="save">

        {{ $this->form }}

        <div style="margin-top:1.5rem">
            <x-filament::button type="submit">
                ذخیره‌ی پروفایل
            </x-filament::button>
        </div>

    </form>

</x-filament-panels::page>
