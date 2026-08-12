<?php

declare(strict_types=1);

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    /**
     * مجوز ارسال درخواست
     */
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->can('videos.create');
    }


    /**
     * قوانین اعتبارسنجی
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | محتوای آموزشی مربوط به ویدئو
            |--------------------------------------------------------------------------
            */

            'content_item_id' => [

                'required',

                'integer',

                'exists:content_items,id',

            ],


            /*
            |--------------------------------------------------------------------------
            | فایل ویدئو
            |--------------------------------------------------------------------------
            */

            'video' => [

                'required',

                'file',

                'mimes:mp4,mov,avi,mkv,webm',

                // حدود 500 مگابایت
                'max:512000',

            ],


            /*
            |--------------------------------------------------------------------------
            | تنظیمات ویدئو
            |--------------------------------------------------------------------------
            */

            'quality' => [

                'nullable',

                'string',

                'max:15',

            ],


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

            'content_item_id.required'
                => 'محتوای آموزشی الزامی است.',

            'content_item_id.exists'
                => 'محتوای آموزشی انتخاب شده معتبر نیست.',


            'video.required'
                => 'فایل ویدئو الزامی است.',

            'video.mimes'
                => 'فرمت ویدئو باید یکی از mp4، mov، avi، mkv یا webm باشد.',

            'video.max'
                => 'حجم ویدئو نباید بیشتر از 500 مگابایت باشد.',


        ];
    }
}
