{{--
    پخش‌کننده‌ی ویدئو برای صفحه‌ی «نمایش» محتوای آموزشی
    --------------------------------------------------------------------
    Filament کامپوننت آماده‌ای برای نمایش ویدئو در Infolist ندارد
    (فقط برای تصویر ImageEntry دارد)، برای همین این View مستقل
    ساخته شده تا بشود واقعاً کلیپ را همانجا دید و بررسی کرد، نه
    فقط یک لینک برای دانلود.
--}}
<div>
    @if ($url = $getRecord()->video?->video_url)
        <video
            controls
            preload="metadata"
            style="width: 100%; max-width: 480px; border-radius: 0.5rem;"
        >
            <source src="{{ $url }}">
            مرورگر شما از پخش ویدئو پشتیبانی نمی‌کند.
        </video>
    @else
        <span class="text-sm text-gray-500">فایلی آپلود نشده است.</span>
    @endif
</div>
