{{--
    تصویر گام‌به‌گام — قابل کلیک برای دیدن در سایز کامل
    --------------------------------------------------------------------
    Filament کامپوننت آماده‌ای برای «کلیک روی عکس = باز شدن در حالت
    بزرگ» ندارد؛ اینجا خودِ عکس داخل یک لینک قرار می‌گیرد که با
    کلیک، فایل اصلی (سایز کامل) را در تب جدید باز می‌کند.
--}}
@php
    $path = $getState();
    $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
@endphp

<div>
    @if ($url)
        <a href="{{ $url }}" target="_blank" rel="noopener">
            <img
                src="{{ $url }}"
                alt="تصویر گام‌به‌گام"
                style="max-height: 220px; border-radius: 0.5rem; cursor: zoom-in;"
            >
        </a>
    @else
        <span class="text-sm text-gray-500">تصویری وجود ندارد.</span>
    @endif
</div>
