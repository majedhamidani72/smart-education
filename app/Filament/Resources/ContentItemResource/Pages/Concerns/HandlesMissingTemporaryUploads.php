<?php

namespace App\Filament\Resources\ContentItemResource\Pages\Concerns;

use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait HandlesMissingTemporaryUploads
{
    /**
     * A browser tab may keep a Livewire upload reference after its temporary
     * file has expired or has been removed. Let the form report a normal
     * required-field error instead of allowing MIME validation to throw a 500.
     */
    protected function beforeValidate(): void
    {
        $removedUpload = false;

        foreach (['video.video_file', 'pdfFile.file'] as $path) {
            $value = data_get($this->data, $path);
            $cleanValue = $this->removeMissingTemporaryUploads($value, $removedUpload);

            if ($cleanValue !== $value) {
                data_set($this->data, $path, $cleanValue);
            }
        }

        // Filament keeps hidden content-type fields in the form state and may
        // explicitly set them to null. data_get's default is only used when a
        // key is absent, so normalize that null before iterating.
        $pages = data_get($this->data, 'stepByStep');

        if (! is_iterable($pages)) {
            $pages = [];
        }

        foreach ($pages as $key => $page) {
            $value = is_array($page) ? ($page['image'] ?? null) : null;
            $cleanValue = $this->removeMissingTemporaryUploads($value, $removedUpload);

            if ($cleanValue !== $value) {
                data_set($this->data, "stepByStep.{$key}.image", $cleanValue);
            }
        }

        if ($removedUpload) {
            Notification::make()
                ->warning()
                ->title('فایل آپلودشده منقضی شده است')
                ->body('لطفاً فایل را دوباره انتخاب کنید و پس از کامل‌شدن آپلود، دوباره روی ثبت بزنید.')
                ->persistent()
                ->send();
        }
    }

    protected function removeMissingTemporaryUploads(mixed $value, bool &$removedUpload): mixed
    {
        if ($value instanceof TemporaryUploadedFile) {
            if (is_file($value->getRealPath())) {
                return $value;
            }

            $removedUpload = true;

            return null;
        }

        if (! is_array($value)) {
            return $value;
        }

        $cleanValue = [];

        foreach ($value as $key => $item) {
            $item = $this->removeMissingTemporaryUploads($item, $removedUpload);

            if ($item !== null) {
                $cleanValue[$key] = $item;
            }
        }

        return $cleanValue;
    }
}
