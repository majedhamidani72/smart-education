<?php

namespace App\Http\Requests\PdfFile;

use Illuminate\Foundation\Http\FormRequest;

class StorePdfFileRequest extends FormRequest
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

            'title' => [
                'required',
                'string',
                'max:150',
            ],

            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:51200',
            ],

        ];
    }
}
