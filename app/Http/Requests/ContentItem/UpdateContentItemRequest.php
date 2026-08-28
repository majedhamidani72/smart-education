<?php

namespace App\Http\Requests\ContentItem;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContentItemRequest extends FormRequest
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
        $contentItem = $this->route('contentItem');

        return [

            'section_id' => [
                'required',
                'exists:sections,id',
            ],

            'content_type_id' => [
                'required',
                'exists:content_types,id',
            ],

            'created_by' => [
                'required',
                'exists:users,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',

                Rule::unique('content_items')
                    ->where(function ($query) {
                        return $query->where(
                            'section_id',
                            $this->section_id
                        );
                    })
                    ->ignore($contentItem),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'page_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'thumbnail' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_free' => [
                'sometimes',
                'boolean',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:1',
            ],

        ];
    }
}
