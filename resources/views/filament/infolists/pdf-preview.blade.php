{{--
    پیش‌نمایش فایل PDF برای صفحه‌ی «نمایش» محتوای آموزشی
    --------------------------------------------------------------------
    بیشتر مرورگرهای امروزی می‌توانند PDF را مستقیم داخل iframe
    نشان بدهند، پس نیازی به دانلود فایل برای بررسی سریع نیست.
--}}
<div>
    @if ($url = $getRecord()->pdfFile?->file_url)
        <iframe
            src="{{ $url }}"
            style="width: 100%; height: 500px; border: 1px solid #e5e7eb; border-radius: 0.5rem;"
        ></iframe>
    @else
        <span class="text-sm text-gray-500">فایلی آپلود نشده است.</span>
    @endif
</div>
