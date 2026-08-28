<?php

namespace App\Filament\Resources\QuestionResource\Pages\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait HandlesMissingQuestionUploads
{
    protected function beforeValidate(): void
    {
        if (! $this->discardMissingQuestionUploads($this->data)) {
            return;
        }

        $this->notifyAboutMissingQuestionUpload();

        throw ValidationException::withMessages([
            'data.image_path' => 'یکی از تصاویر آپلودشده منقضی شده است؛ لطفاً آن را دوباره انتخاب کنید.',
        ]);
    }

    protected function discardMissingQuestionUploads(?array &$state): bool
    {
        $removedUpload = false;
        $state ??= [];
        $state = $this->removeMissingQuestionUploads($state, $removedUpload);

        return $removedUpload;
    }

    protected function notifyAboutMissingQuestionUpload(): void
    {
        Notification::make()
            ->warning()
            ->title('تصویر آپلودشده منقضی شده است')
            ->body('لطفاً تصویر سؤال، گزینه یا پاسخ تشریحی را دوباره انتخاب کنید و پس از کامل‌شدن آپلود، دوباره ثبت کنید.')
            ->persistent()
            ->send();
    }

    protected function removeMissingQuestionUploads(mixed $value, bool &$removedUpload): mixed
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
            $item = $this->removeMissingQuestionUploads($item, $removedUpload);

            if ($item !== null) {
                $cleanValue[$key] = $item;
            }
        }

        return $cleanValue;
    }
}
