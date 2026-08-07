<?php

namespace App\Http\Requests\StepByStepPage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStepByStepPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'content_item_id' => [
                'sometimes',
                'exists:content_items,id',
            ],

            'page_number' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'image' => [
                'nullable',
                'image',
                'max:5120',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'is_free' => [
                'sometimes',
                'boolean',
            ],

        ];
    }
}
