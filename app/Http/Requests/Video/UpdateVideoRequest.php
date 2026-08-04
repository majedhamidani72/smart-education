<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    /**
     * مجوز ارسال درخواست
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قوانین اعتبارسنجی
     */
    public function rules(): array
    {
        return [

            'content_item_id' => 'required|exists:content_items,id',

            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:512000',

            'quality' => 'nullable|string|max:20',

            'download_allowed' => 'sometimes|boolean',

        ];
    }
}
