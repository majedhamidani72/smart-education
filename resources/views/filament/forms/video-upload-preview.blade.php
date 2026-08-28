@php
    use Illuminate\Support\Facades\Storage;
    use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

    $videoFile = is_array($file ?? null)
        ? collect($file)->first()
        : ($file ?? null);

    $videoUrl = null;

    try {
        if ($videoFile instanceof TemporaryUploadedFile && is_file($videoFile->getRealPath())) {
            $videoUrl = $videoFile->temporaryUrl();
        } elseif (is_string($videoFile) && filled($videoFile)) {
            $videoUrl = Storage::disk('public')->url($videoFile);
        }
    } catch (Throwable) {
        $videoUrl = null;
    }
@endphp

@if ($videoUrl)
    {{-- Same responsive 16:9 player used by the public website. Native video
         controls include the browser's fullscreen button. --}}
    <div style="width:100%; max-width:760px; margin-inline:auto;">
        <div style="position:relative; width:100%; aspect-ratio:16/9; overflow:hidden; border-radius:1rem; background:#0f172a; box-shadow:0 10px 25px rgba(15,23,42,.18);">
            <video
                controls
                playsinline
                preload="metadata"
                style="display:block; width:100%; height:100%; object-fit:cover;"
            >
                <source src="{{ $videoUrl }}">
                مرورگر شما از پخش ویدئو پشتیبانی نمی‌کند.
            </video>
        </div>
        <p style="margin-top:.6rem; text-align:center; font-size:.75rem; color:#64748b;">
            برای نمایش تمام‌صفحه، از دکمهٔ تمام‌صفحه داخل پلیر استفاده کنید.
        </p>
    </div>
@endif
