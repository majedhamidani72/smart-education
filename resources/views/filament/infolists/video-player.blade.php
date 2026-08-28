{{--
    پخش‌کننده‌ی ویدئو برای صفحه‌ی «نمایش» محتوای آموزشی
    --------------------------------------------------------------------
    Filament کامپوننت آماده‌ای برای نمایش ویدئو در Infolist ندارد
    (فقط برای تصویر ImageEntry دارد)، برای همین این View مستقل
    ساخته شده تا بشود واقعاً کلیپ را همانجا دید و بررسی کرد، نه
    فقط یک لینک برای دانلود.
--}}
<div style="padding: 1.25rem; border: 1px solid #fed7aa; border-radius: 1.5rem; background: #fffaf3;">
    @if ($url = $getRecord()->video?->video_url)
        <div style="margin-bottom: 1rem;">
            <strong style="display:block; font-size:1.05rem; color:#1e293b;">{{ $getRecord()->title }}</strong>
            @if ($getRecord()->page_number)
                <span style="display:block; margin-top:.25rem; font-size:.8rem; color:#64748b;">{{ $getRecord()->page_number }}</span>
            @endif
        </div>
        <div style="width:100%; max-width:640px; aspect-ratio:16/9; margin:auto; overflow:hidden; border-radius:1rem; background:#000; box-shadow:0 10px 30px rgba(15,23,42,.18);">
            <video controls playsinline preload="metadata" style="width:100%; height:100%; object-fit:cover;">
                <source src="{{ $url }}">
                مرورگر شما از پخش ویدئو پشتیبانی نمی‌کند.
            </video>
        </div>
    @else
        <span class="text-sm text-gray-500">فایلی آپلود نشده است.</span>
    @endif
</div>
