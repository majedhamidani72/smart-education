<?php

namespace App\Http\Requests\StepByStepPage;

use Illuminate\Foundation\Http\FormRequest;

class StoreStepByStepPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'content_item_id' => [
                'required',
                'exists:content_items,id',
            ],

            'page_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'image' => [
                'required',
                'image',
                'max:5120',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:1',
            ],

            'is_free' => [
                'required',
                'boolean',
            ],

        ];
    }
}
