<?php

declare(strict_types=1);

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    /**
     * مجوز ارسال درخواست
     */
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->can('videos.update');
    }


    /**
     * قوانین اعتبارسنجی
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | محتوای آموزشی
            |--------------------------------------------------------------------------
            */

            'content_item_id' => [

                'sometimes',

                'integer',

                'exists:content_items,id',

            ],


            /*
            |--------------------------------------------------------------------------
            | فایل جدید ویدئو
            |--------------------------------------------------------------------------
            */

            'video' => [

                'nullable',

                'file',

                'mimes:mp4,mov,avi,mkv,webm',

                // حدود 500 مگابایت

                'max:512000',

            ],


            /*
            |--------------------------------------------------------------------------
            | کیفیت ویدئو
            |--------------------------------------------------------------------------
            */

            'quality' => [

                'nullable',

                'string',

                'max:15',

            ],


            /*
            |--------------------------------------------------------------------------
            | اجازه دانلود
            |--------------------------------------------------------------------------
            */

            'download_allowed' => [

                'nullable',

                'boolean',

            ],

        ];
    }


    /**
     * پیام‌های خطا
     */
    public function messages(): array
    {
        return [

            'content_item_id.exists'
                => 'محتوای آموزشی انتخاب شده معتبر نیست.',


            'video.mimes'
                => 'فرمت ویدئو باید یکی از mp4، mov، avi، mkv یا webm باشد.',


            'video.max'
                => 'حجم ویدئو نباید بیشتر از 500 مگابایت باشد.',

        ];
    }
}
