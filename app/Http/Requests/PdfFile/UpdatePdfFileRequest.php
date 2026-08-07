<?php

namespace App\Http\Requests\PdfFile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePdfFileRequest extends FormRequest
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

            'title' => [
                'sometimes',
                'string',
                'max:150',
            ],

            'pdf' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:51200',
            ],

        ];
    }
}
